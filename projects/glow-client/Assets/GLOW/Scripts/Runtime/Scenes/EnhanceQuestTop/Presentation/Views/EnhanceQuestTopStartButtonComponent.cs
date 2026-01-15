using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EnhanceQuestTop.Presentation.Views
{
    public class EnhanceQuestTopStartButtonComponent : UIObject
    {
        [Header("通常開始ボタン")]
        [SerializeField] Button _startButton;
        [SerializeField] UIText _challengeCountText;
        [SerializeField] UIObject _challengeCountTextObject;
        [SerializeField] UIObject _startButtonGrayout;

        [Header("広告ボタン")]
        [SerializeField] Button _adButton;
        [SerializeField] UIText _adCountText;
        [SerializeField] UIText _cantAdCountText;

        [Header("広告スキップボタン")]
        [SerializeField] Button _adSkipButton;
        [SerializeField] UIText _adSkipPassNameText;
        [SerializeField] UIText _adSkipCountText;
        [SerializeField] UIText _cantAdSkipCountText;

        [Header("編成ボタン")]
        [SerializeField] Button _partyButton;


        public void Setup(
            EnhanceQuestChallengeCount challengeCount,
            EnhanceQuestChallengeCount adChallengeCount,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            bool shouldShowStartButton = challengeCount.IsEnough() //通常挑戦できるとき表示
                || (!challengeCount.IsEnough() && !adChallengeCount.IsEnough());// 通常・広告挑戦どちらもできないとき表示
            _startButton.gameObject.SetActive(shouldShowStartButton);
            _startButtonGrayout.IsVisible = !challengeCount.IsEnough();
            _challengeCountText.SetText(ToChallengeText(challengeCount));
            _challengeCountTextObject.Hidden = !challengeCount.IsEnough();

            if (heldAdSkipPassInfoViewModel.PassProductName.IsEmpty())
            {
                SetUpAdChallengeButton(challengeCount, adChallengeCount);
            }
            else
            {
                SetUpAdSkipChallengeButton(challengeCount, adChallengeCount, heldAdSkipPassInfoViewModel.PassProductName);
            }

        }

        void SetUpAdChallengeButton(
            EnhanceQuestChallengeCount challengeCount,
            EnhanceQuestChallengeCount adChallengeCount)
        {
            _adSkipButton.gameObject.SetActive(false);
            bool isEnoughAdChallengeCount = !challengeCount.IsEnough() && adChallengeCount.IsEnough();// 広告挑戦できるとき表示
            _adButton.gameObject.SetActive(isEnoughAdChallengeCount);
            _adCountText.SetText(ToAdChallengeText(adChallengeCount));
            _cantAdCountText.Hidden = isEnoughAdChallengeCount;

            _adSkipButton.gameObject.SetActive(false);
        }

        void SetUpAdSkipChallengeButton(
            EnhanceQuestChallengeCount challengeCount,
            EnhanceQuestChallengeCount adChallengeCount,
            PassProductName passProductName)
        {
            _adButton.gameObject.SetActive(false);
            bool isEnoughAdChallengeCount = !challengeCount.IsEnough() && adChallengeCount.IsEnough();// 広告挑戦できるとき表示
            _adSkipButton.gameObject.SetActive(isEnoughAdChallengeCount);
            _adSkipCountText.SetText(ToAdChallengeText(adChallengeCount));
            _cantAdSkipCountText.Hidden = isEnoughAdChallengeCount;

            _adSkipPassNameText.SetText(ZString.Format("{0}適用中", passProductName.ToString()));

            _adButton.gameObject.SetActive(false);
        }

        string ToChallengeText(EnhanceQuestChallengeCount challengeCount)
        {
            return ZString.Format("本日あと<color={0}>{1}回</color>挑戦可能", ColorCodeTheme.TextRed, challengeCount.Value);
        }
        string ToAdChallengeText(EnhanceQuestChallengeCount challengeCount)
        {
            return ZString.Format("本日あと<color={0}>{1}回無料</color>で挑戦可能", ColorCodeTheme.TextRed, challengeCount.Value);
        }
    }
}
