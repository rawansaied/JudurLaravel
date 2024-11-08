<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $chatbotService;
    protected $faqs;
    protected $shownQuestions = [];

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;

        $this->faqs = [
            'registration' => [
                'How can I register on the Judur platform?' => 'You can register on the Judur platform by visiting the registration page and creating an account. During registration, you will select your role: Provider, Donor, Landowner, or Volunteer. After completing the registration form, your account will be verified by an Admin to ensure authenticity.',
                'How does Judur verify the authenticity of users?' => 'Judur conducts a verification process for all users during registration. Admins review the submitted information and confirm that the accounts are legitimate to prevent fraudulent activities.',
            ],
            'donations' => [
                'What types of donations do you accept?' => 'We accept a variety of donations, including large-scale food supplies, clothing, valuable items, personal items, and spaces for feeding events. Providers typically donate substantial items in bulk, while Donors can contribute smaller items such as personal goods or valuable auction items.',
                'Can I donate valuable items for auction?' => 'Yes, you can donate valuable items like antiques, electronics, or other high-value goods, which can be auctioned on our platform. The proceeds from the auction will be allocated to feeding programs and charitable distributions.',
                'How do I submit a donation as a Provider or Donor?' => 'After logging into the platform, Providers can fill out a form specifying the type, quantity, and condition of their donation. Donors will fill out a simpler form detailing their personal item donations. If you are donating valuable goods, you can opt for auctioning them during this process.',
                'How do I track the impact of my donation?' => 'Once logged in, Providers, Donors, and Landowners can access their dashboards to track where their donations were used. They receive updates on feeding events, auction outcomes, and even feedback on how many people were helped with their contribution.',
                'Can I see where the funds from my donation or auction were used?' => 'Yes, Judur provides transparency through detailed financial reports. You will be able to track auction proceeds and see how the funds were allocated to support feeding events and other charitable activities.',
                'What happens to unsold auction items?' => 'If auction items remain unsold, the Admin will decide whether to relist them in a future auction, donate them to other charitable causes, or return them to the original donor based on the item\'s condition and potential for future sale.',
            ],
            'auctions' => [
                'How do auctions work on the platform?' => 'Auction items are listed in the Auction section of the platform, and verified users can place bids. When the auction ends, the highest bidder wins, and the funds are distributed to support feeding programs for the needy.',
                'How can I participate in an auction?' => 'Verified users can participate in auctions by bidding on listed valuable items. You will need to log into your account to access the Auction section, place bids, and track the auction outcomes.',
                'What payment methods are available for auctions?' => 'For secure payment processing, Judur integrates with PayPal, allowing users to make payments through various methods including credit cards, PayPal accounts, and other supported options.',
            ],
            'roles' => [
                'What role do Landowners play in the Judur platform?' => 'Landowners offer their spaces for hosting charitable feeding operations. They submit details about their property, including its location, facilities, and capacity. After an Admin review, an Examiner evaluates the suitability of the space for hosting feeding events.',
                'What is the role of Examiners in Judur?' => 'Examiners (a type of Volunteer) assess the suitability of donated spaces for feeding operations. They visit the location to check its capacity, facilities, safety, and accessibility. After the evaluation, they submit a report to the Admin, who makes the final approval.',
                'How can I volunteer for events?' => 'Volunteers can register on the platform, and after Admin verification, they can view upcoming events through their dashboard. They will be assigned to specific events, where they can manage the distribution of donated goods and help organize feeding events.',
            ],
            'events' => [
                'How can I stay updated on the status of my donation or event?' => 'The platform offers Real-Time Notifications, keeping users informed about successful bids, payment confirmations, auction outcomes, and event updates. Notifications are sent to your dashboard and via email.',
                'How can I get involved in organizing feeding events?' => 'Volunteers interested in organizing feeding events can register on the platform and specify their interest in event management. Admins will assign them to relevant tasks based on their experience and availability.',
            ],
            'feedback' => [
                'What kind of feedback can I provide as a user?' => 'Users can provide feedback on their experiences with the donation process, the effectiveness of feeding events, and the overall functionality of the Judur platform. This feedback helps improve the platform and enhance user experiences.',
            ],
            'support' => [
                'How can I contact support if I have issues or questions?' => 'Users can contact Judur support through the "Contact Us" section on the website. Alternatively, you can reach out via email or through the support form, and a team member will respond promptly to assist you.',
            ],
        ];
    }

    public function respond(Request $request)
    {
        $userMessage = $request->input('message', '');

        if (empty($userMessage)) {
            $nextQuestions = $this->getNextFAQs();
            return response()->json([
                'answer' => 'Hello! How can I assist you today?', 
                'suggestions' => $nextQuestions
            ]);
        }

        $faqResponse = $this->checkFAQ($userMessage);
        if ($faqResponse) {
            $nextQuestions = $this->getNextFAQs($userMessage);
            return response()->json([
                'answer' => $faqResponse, 
                'suggestions' => $nextQuestions
            ]);
        }

        $response = $this->chatbotService->getChatbotResponse($userMessage);
        return response()->json([
            'answer' => $response, 
            'suggestions' => $this->getNextFAQs()
        ]);
    }

    protected function checkFAQ($message)
    {
        foreach ($this->faqs as $category => $questions) {
            if (isset($questions[$message])) {
                $this->shownQuestions[] = $message;
                return $questions[$message];
            }
        }
        return null;
    }

    protected function getNextFAQs($currentQuestion = null)
    {
        $availableQuestions = [];
        
        // Collect all questions except those already shown
        foreach ($this->faqs as $category => $questions) {
            foreach ($questions as $question => $answer) {
                if (!in_array($question, $this->shownQuestions)) {
                    $availableQuestions[] = $question;
                }
            }
        }

        // Return next three questions if available
        return array_slice($availableQuestions, 0, 3);
    }
}
