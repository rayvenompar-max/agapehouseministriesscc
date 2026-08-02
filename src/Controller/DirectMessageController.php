<?php
/**
 * Controller\DirectMessageController
 *
 * POST   /api/messages/start/{memberId}    → startConversation() — start a conversation with a member
 * GET    /api/messages/conversations        → getConversations()  — get all conversations for current member
 * GET    /api/messages/conversation/{id}    → getConversation()   — get messages in a conversation
 * POST   /api/messages/conversation/{id}    → sendMessage()       — send a message in a conversation
 * POST   /api/messages/conversation/{id}/read → markAsRead()      — mark conversation as read
 */
declare(strict_types=1);

namespace Controller;

use Repository\DirectMessageRepository;
use Repository\MemberRepository;

class DirectMessageController extends BaseController
{
    public function __construct(
        private readonly DirectMessageRepository $dmRepo,
        private readonly MemberRepository $memberRepo
    ) {}

    /**
     * POST /api/messages/start/{memberId}
     * Start a conversation with another member (or get existing conversation)
     */
    public function startConversation(int $targetMemberId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $currentMemberId = (int) $_SESSION['member']['id'];

        if ($currentMemberId === $targetMemberId) {
            $this->error('Cannot message yourself.', 400);
        }

        // Check if target member exists
        $targetMember = $this->memberRepo->findById($targetMemberId);
        if (!$targetMember) {
            $this->error('Member not found.', 404);
        }

        try {
            $conversation = $this->dmRepo->findOrCreateConversation($currentMemberId, $targetMemberId);
            
            $this->success([
                'conversation_id' => $conversation->id,
                'other_member' => [
                    'id'              => $targetMember->id,
                    'display_name'    => $targetMember->displayName,
                    'username'        => $targetMember->username,
                    'profile_picture' => $targetMember->profilePicture,
                ],
            ], 'Conversation ready.');
        } catch (\Exception $e) {
            $this->error('Could not start conversation.', 500);
        }
    }

    /**
     * GET /api/messages/conversations
     * Get all conversations for the current member
     */
    public function getConversations(): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];

        try {
            $conversations = $this->dmRepo->findConversationsByMember($memberId);
            $this->success($conversations);
        } catch (\Exception $e) {
            $this->error('Could not load conversations.', 500);
        }
    }

    /**
     * GET /api/messages/conversation/{id}
     * Get all messages in a conversation (must be a participant)
     */
    public function getConversation(int $conversationId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];

        try {
            $conversation = $this->dmRepo->findConversationById($conversationId);

            if (!$conversation) {
                $this->error('Conversation not found.', 404);
            }

            // Verify member is part of this conversation
            if (!$conversation->hasMember($memberId)) {
                $this->error('Unauthorised.', 403);
            }

            $messages = $this->dmRepo->findMessagesByConversation($conversationId);

            // Get other member info
            $otherMemberId = $conversation->getOtherMemberId($memberId);
            $otherMember = $this->memberRepo->findById($otherMemberId);

            $this->success([
                'conversation' => $conversation->toArray(),
                'other_member' => $otherMember ? [
                    'id'              => $otherMember->id,
                    'display_name'    => $otherMember->displayName,
                    'username'        => $otherMember->username,
                    'profile_picture' => $otherMember->profilePicture,
                ] : null,
                'messages' => $messages,
            ]);
        } catch (\Exception $e) {
            $this->error('Could not load conversation.', 500);
        }
    }

    /**
     * POST /api/messages/conversation/{id}
     * Send a message in a conversation
     */
    public function sendMessage(int $conversationId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];
        $body = $this->getJsonBody();

        if (!$body || empty($body['body'])) {
            $this->error('Message body is required.', 400);
        }

        try {
            $conversation = $this->dmRepo->findConversationById($conversationId);

            if (!$conversation) {
                $this->error('Conversation not found.', 404);
            }

            // Verify member is part of this conversation
            if (!$conversation->hasMember($memberId)) {
                $this->error('Unauthorised.', 403);
            }

            $message = $this->dmRepo->addMessage(
                $conversationId,
                $memberId,
                trim($body['body'])
            );

            // Get sender info
            $sender = $this->memberRepo->findById($memberId);

            $this->success([
                'id'              => $message->id,
                'conversation_id' => $message->conversationId,
                'sender_id'       => $message->senderId,
                'body'            => $message->body,
                'is_read'         => $message->isRead,
                'created_at'      => $message->createdAt,
                'sender_name'     => $sender?->displayName ?? 'Member',
                'sender_username' => $sender?->username ?? '',
                'sender_picture'  => $sender?->profilePicture ?? null,
            ], 'Message sent.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->error('Could not send message.', 500);
        }
    }

    /**
     * POST /api/messages/conversation/{id}/read
     * Mark all messages in a conversation as read
     */
    public function markAsRead(int $conversationId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
        }

        $memberId = (int) $_SESSION['member']['id'];

        try {
            $conversation = $this->dmRepo->findConversationById($conversationId);

            if (!$conversation) {
                $this->error('Conversation not found.', 404);
            }

            if (!$conversation->hasMember($memberId)) {
                $this->error('Unauthorised.', 403);
            }

            $this->dmRepo->markConversationAsRead($conversationId, $memberId);
            $this->success(null, 'Marked as read.');
        } catch (\Exception $e) {
            $this->error('Could not mark as read.', 500);
        }
    }
}
