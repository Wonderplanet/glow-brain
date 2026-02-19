using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Domain.ValueObjects.StaminaRecover;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverSelect
{
    public class StaminaRecoverSelectView : UIView
    {
        [Header("モーダルヘッダー・説明")]
        [SerializeField] UIText _headerText;
        [SerializeField] GameObject _headerParentObj;
        [SerializeField] UIText _descriptionText;

        [Header("石回復ボタン")]
        [SerializeField] UIText _diamondRecoverValueText;
        [SerializeField] UIText _diamondConsumeValueText;

        [Header("動画広告回復量")]
        [SerializeField] UIText _adRecoverValueText;

        [Header("動画広告回復可能回数")]
        [SerializeField] UIText _advLeftRecoverCountText;

        [Header("動画広告回復ボタン")]
        [SerializeField] GameObject _adButtonObject;
        [SerializeField] Button _adButton;

        [Header("動画広告回復ボタン/利用可否")]
        [SerializeField] GameObject _adUsableTextObject;
        [SerializeField] UIText _advRecoverIntervalText;
        [SerializeField] GameObject _advRecoverIntervalTextObject;

        [Header("パス適用広告スキップ回復ボタン")]
        [SerializeField] GameObject _adSkipButtonObject;
        [SerializeField] Button _adSkipButton;
        [SerializeField] UIText _adSkipPassProductNameText;

        [Header("パス適用広告スキップ回復ボタン/利用可否")]
        [SerializeField] UIText _advSkipRecoverIntervalText;
        [SerializeField] GameObject _advSkipRecoverIntervalTextObject;

        public void Setup(StaminaRecoverSelectViewModel viewModel)
        {
            //タイトル・説明文
            _headerText.SetText(viewModel.HeaderTitle);
            _descriptionText.SetText(viewModel.Description);
            _headerParentObj.SetActive(!string.IsNullOrEmpty(viewModel.Description));

            //広告回復/広告スキップ
            SetUpAdvertiseButton(
                viewModel.AdvRecoverStaminaValue,
                viewModel.IsAdStaminaRecoverable,
                viewModel.RemainingAdRecoverCount,
                viewModel.HeldAdSkipPassInfoViewModel);

            //Diamond回復
            _diamondRecoverValueText.SetText("{0}", viewModel.DiamondRecoverStaminaValue.Value);
            _diamondConsumeValueText.SetText("{0}",viewModel.ConsumeDiamondValue.Value);
        }

        public void UpdateAdRecoverInterval(bool isUsable, string intervalTime)
        {
            //回復利用可能(最大回数でない、最大スタミナでない、時間経過してない)
            _adUsableTextObject.SetActive(isUsable || string.IsNullOrEmpty(intervalTime));
            //回復不能かどうか
            _adButton.interactable = isUsable;
            _adSkipButton.interactable = isUsable;

            //時間待ち表示
            _advRecoverIntervalText.SetText(intervalTime);
            _advRecoverIntervalTextObject.SetActive(!string.IsNullOrEmpty(intervalTime));
            _advSkipRecoverIntervalText.SetText(intervalTime);
            _advSkipRecoverIntervalTextObject.SetActive(!string.IsNullOrEmpty(intervalTime));
        }

        void SetUpAdvertiseButton(
            Stamina advRecoverStaminaValue,
            StaminaRecoveryFlag isStaminaCanRecover,
            BuyStaminaAdCount buyStaminaAdCount,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            _advLeftRecoverCountText.SetText("本日あと{0}回", buyStaminaAdCount.Value);
            _adRecoverValueText.SetText("{0}", advRecoverStaminaValue.Value);

            _adButtonObject.SetActive(heldAdSkipPassInfoViewModel.IsEmpty());
            _adSkipButtonObject.SetActive(!heldAdSkipPassInfoViewModel.IsEmpty());

            if (heldAdSkipPassInfoViewModel.IsEmpty())
            {
                // 広告回復
                _adButton.interactable = isStaminaCanRecover.Value;
            }
            else
            {
                // 広告スキップ
                _adSkipButton.interactable = isStaminaCanRecover.Value;
                _adSkipPassProductNameText.SetText(ZString.Format(
                    "{0}適用中",
                    heldAdSkipPassInfoViewModel.PassProductName.ToString()));
            }
        }
    }
}
