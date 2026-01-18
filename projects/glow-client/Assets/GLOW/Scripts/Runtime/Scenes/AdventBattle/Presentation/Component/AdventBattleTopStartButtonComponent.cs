using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleTopStartButtonComponent : UIObject
    {
        [Header("開始ボタン")]
        [SerializeField] Button _startButton;
        [SerializeField] UIImage _startButtonImage;
        [SerializeField] Sprite _normalStartButtonSprite;
        [SerializeField] Sprite _adStartButtonSprite;
        [SerializeField] Sprite _adSkipStartButtonSprite;
        [SerializeField] UIObject _startButtonTextObject;
        [SerializeField] UIObject _adStartButtonTextObject;
        [SerializeField] UIObject _adSkipStartButtonTextObject;
        [SerializeField] UIText _adSkipStartButtonPassNameText;
        [SerializeField] UIObject _startButtonGrayoutObject;

        [Header("通常開始ボタン/挑戦可能")]
        [SerializeField] UIObject _challengeableCountObject;
        [SerializeField] UIText _challengeableCountText;
        [Header("通常開始ボタン/挑戦不可能")]
        [SerializeField] UIText _cantChallengeCountText;

        [Header("編成ボタン")]
        [SerializeField] Button _partyButton;
        [SerializeField] UIText _partyNumberText;

        public void Setup(
            AdventBattleChallengeCount challengeCount,
            AdventBattleChallengeCount adChallengeCount,
            PartyName partyName,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            SetPartyName(partyName);

            if (!challengeCount.IsZero())
            {
                // 通常の挑戦回数がまだ残っている場合
                _challengeableCountObject.Hidden = false;
                _challengeableCountText.SetText(CreateChallengeButtonString(challengeCount));
                _cantChallengeCountText.gameObject.SetActive(false);
                _startButtonGrayoutObject.IsVisible = false;

                _startButtonImage.Image.sprite = _normalStartButtonSprite;
                _startButtonTextObject.Hidden = false;
                _adStartButtonTextObject.Hidden = true;
            }
            else if (!adChallengeCount.IsZero())
            {
                if (heldAdSkipPassInfoViewModel.IsEmpty())
                {
                    SetUpAdChallengeButton(adChallengeCount);
                }
                else
                {
                    SetUpAdSkipChallengeButton(adChallengeCount, heldAdSkipPassInfoViewModel.PassProductName);
                }
            }
            else
            {
                // 通常の挑戦回数、広告での挑戦回数共に残っていない場合
                _startButtonGrayoutObject.IsVisible = true;
                _startButtonImage.Image.sprite = _normalStartButtonSprite;
                _startButtonTextObject.Hidden = false;
                _adStartButtonTextObject.Hidden = true;
                _challengeableCountObject.Hidden = true;
                _cantChallengeCountText.gameObject.SetActive(true);
                _cantChallengeCountText.SetText("本日分は終了しました");
            }
        }

        public void SetButtonInteractable(bool interactable)
        {
            _partyButton.interactable = interactable;
            _startButton.interactable = interactable;
        }

        public void SetPartyName(PartyName partyName)
        {
            _partyNumberText.SetText(partyName.Value);
        }

        void SetUpAdChallengeButton(AdventBattleChallengeCount adChallengeCount)
        {
            // 通常の挑戦回数が残っていない、かつ広告での挑戦回数が残っている場合
            _challengeableCountObject.Hidden = false;
            _challengeableCountText.SetText(CreateAdChallengeButtonString(adChallengeCount));
            _cantChallengeCountText.gameObject.SetActive(false);
            _startButtonGrayoutObject.IsVisible = false;

            _startButtonImage.Image.sprite = _adStartButtonSprite;
            _startButtonTextObject.Hidden = true;
            _adSkipStartButtonTextObject.Hidden = true;
            _adStartButtonTextObject.Hidden = false;
        }

        void SetUpAdSkipChallengeButton(AdventBattleChallengeCount adChallengeCount, PassProductName passProductName)
        {
            // 通常の挑戦回数が残っていない、かつ広告での挑戦回数が残っている場合、かつ広告スキップの時
            _challengeableCountObject.Hidden = false;
            _challengeableCountText.SetText(CreateAdChallengeButtonString(adChallengeCount));
            _cantChallengeCountText.gameObject.SetActive(false);
            _startButtonGrayoutObject.IsVisible = false;

            _startButtonImage.Image.sprite = _adSkipStartButtonSprite;
            _startButtonTextObject.Hidden = true;
            _adStartButtonTextObject.Hidden = true;
            _adSkipStartButtonTextObject.Hidden = false;

            _adSkipStartButtonPassNameText.SetText(ZString.Format("{0}適用中", passProductName.ToString()));
        }

        string CreateChallengeButtonString(AdventBattleChallengeCount challengeCount)
        {
            return ZString.Format("本日あと<color={0}>{1}回</color>挑戦可能", ColorCodeTheme.TextRed, challengeCount.Value);
        }

        string CreateAdChallengeButtonString(AdventBattleChallengeCount challengeCount)
        {
            return ZString.Format("本日あと<color={0}>{1}回無料</color>で挑戦可能", ColorCodeTheme.TextRed, challengeCount.Value);
        }
    }
}
