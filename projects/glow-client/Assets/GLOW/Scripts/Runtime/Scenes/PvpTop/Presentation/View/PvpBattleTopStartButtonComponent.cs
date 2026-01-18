using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PvpTop.Presentation
{
    public class PvpBattleTopStartButtonComponent : UIObject
    {
        [Header("開始ボタン")]
        [SerializeField] Button _startButton;
        [Header("開始ボタン/通常挑戦")]
        [SerializeField] UIObject _startButtonImageObject;
        [Header("開始ボタン/チケット消費挑戦")]
        [SerializeField] UIObject _ticketButtonImageObject;
        [SerializeField] UIText _ticketConsumeText;

        [Header("開始ボタン/挑戦可能回数")]
        [SerializeField] UIObject _challengeableBalloonObject;
        [SerializeField] UIText _challengeableCountText;
        [Header("開始ボタン/挑戦不可能")]
        [SerializeField] UIText _cantChallengeCountText;

        [Header("編成ボタン")]
        [SerializeField] Button _partyButton;
        [SerializeField] UIText _partyName;
        [SerializeField] UIObject _specialRuleIconObject;


        bool _isAdventBattleChallengeable;

        public void Initialize()
        {
            // 表示初期化
            _startButtonImageObject.IsVisible = true;
            _ticketButtonImageObject.IsVisible = false;
            _challengeableBalloonObject.IsVisible = false;
            SetStartButtonInteractable(false);
            _cantChallengeCountText.IsVisible = false;
            _partyButton.interactable = false;
        }
        
        public void SetSpecialRuleIconVisible(bool isVisible)
        {
            _specialRuleIconObject.IsVisible = isVisible;
        }

        public void SetUpEditAndStartButton(PartyName partyName, PvpChallengeStatus status)
        {
            SetUpPartyName(partyName);
            SetUpStartButton(status);
        }

        public void SetUpPartyName(PartyName partyName)
        {
            _partyName.SetText(partyName.Value);
        }

        void SetUpStartButton(PvpChallengeStatus status)
        {
            switch (status.Type)
            {
                case PvpChallengeType.Normal:
                    SetUpNormalStartButton(status.ChallengeableCount.Value);
                    break;
                case PvpChallengeType.Ticket:
                    SetUpTicketStartButton(status.ChallengeableCount.Value,status.PvpItemChallengeCost.Value);
                    break;
                case PvpChallengeType.NotChallengeable:
                    SetUpNotChallengeableStartButton();
                    break;
            }
        }

        void SetUpNormalStartButton(int challengeableCount)
        {
            _startButtonImageObject.IsVisible = true;
            _challengeableBalloonObject.IsVisible = true;
            _challengeableCountText.SetText(
                ZString.Format(
                    "本日あと<color={0}>{1}回</color>挑戦可能",
                    ColorCodeTheme.TextRed,
                    challengeableCount));
            _cantChallengeCountText.IsVisible = false;
            _partyButton.interactable = true;
        }

        void SetUpTicketStartButton(int challengeableCount,int cost)
        {
            _ticketButtonImageObject.IsVisible = true;
            _startButtonImageObject.IsVisible = false;

            if (challengeableCount <= 0)
            {
                _ticketConsumeText.SetText("チケット不足");
            }
            else
            {
                var ticketStartFormat = "×{0}";
                _ticketConsumeText.SetText(ticketStartFormat, cost);
            }
            _partyButton.interactable = true;
        }

        void SetUpNotChallengeableStartButton()
        {
            _startButtonImageObject.IsVisible = true;
            _startButton.interactable = false;
            SetStartButtonInteractable(false);
            _cantChallengeCountText.IsVisible = true;
            _cantChallengeCountText.SetText("本日分は終了しました");
            _partyButton.interactable = false;
        }

        public void SetStartButtonInteractable(bool interactable)
        {
            _startButton.interactable = interactable;
        }

    }
}
