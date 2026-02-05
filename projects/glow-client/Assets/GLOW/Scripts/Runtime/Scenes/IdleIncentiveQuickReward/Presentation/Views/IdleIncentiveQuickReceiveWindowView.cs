using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Pass;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.IdleIncentiveQuickReward.Presentation.ViewModels;
using GLOW.Scenes.IdleIncentiveTop.Presentation.Views;
using GLOW.Scenes.PassShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.IdleIncentiveQuickReward.Presentation.Views
{
    public class IdleIncentiveQuickReceiveWindowView : UIView
    {
        [SerializeField] IdleIncentiveRewardList _rewardList;
        [SerializeField] UIText _quickReceiveTimeText;

        [Header("アイテムボタン")]
        [SerializeField] Button _consumeDiamondButton;
        [SerializeField] UIText _requireItemText;
        [SerializeField] UIText _remainConsumeItemCountText;

        [Header("動画広告ボタン")]
        [SerializeField] UIObject _adButtonObject;
        [SerializeField] UITextButton _adButton;
        [SerializeField] UIText _remainAdCountText;
        [SerializeField] GameObject _quickAdIntervalTextObject;
        [SerializeField] UIText _quickAdReceiveIntervalText;
        [SerializeField] GameObject _adButtonTextObj;

        [Header("パス効果で広告スキップボタン")]
        [SerializeField] UIObject _adSkipButtonObject;
        [SerializeField] UITextButton _adSkipButton;
        [SerializeField] GameObject _quickAdSkipIntervalTextObject;
        [SerializeField] UIText _quickAdSkipReceiveIntervalText;

        public void Setup(IdleIncentiveQuickReceiveWindowViewModel viewModel)
        {
            _quickAdIntervalTextObject.SetActive(viewModel.AdCount.Value > 0);
            _quickAdSkipIntervalTextObject.SetActive(viewModel.AdCount.Value > 0);

            _remainAdCountText.SetText("本日あと<color={0}>{1}回</color>", ColorCodeTheme.TextRed, viewModel.AdCount.Value);
            _consumeDiamondButton.interactable = !viewModel.ConsumeItemCount.IsZero();
            _requireItemText.SetText("×{0}",viewModel.RequireItemAmount.Value);
            _remainConsumeItemCountText.SetText("本日あと<color={0}>{1}回</color>", ColorCodeTheme.TextRed, viewModel.ConsumeItemCount.Value.ToString());
            _quickReceiveTimeText.SetText("{0}分の探索報酬を獲得しよう!", viewModel.QuickRewardTimeSpan.TotalMinutes);

            if (viewModel.HeldAdSkipPassInfoViewModel.IsEmpty())
            {
                _adButtonObject.Hidden = false;
                _adSkipButtonObject.Hidden = true;
            }
            else
            {
                _adButtonObject.Hidden = true;
                _adSkipButtonObject.Hidden = false;
                _adSkipButton.TitleText.SetText(ZString.Format(
                    "{0}適用中",
                    viewModel.HeldAdSkipPassInfoViewModel.PassProductName.ToString()));
            }

            _rewardList.Setup(viewModel.RewardList);
        }

        public void PlayCellAppearanceAnimation()
        {
            _rewardList.PlayCellAppearanceAnimation();
        }

        public void UpdateQuickAdReceiveInterval(
            string intervalTime,
            HeldAdSkipPassInfoViewModel heldAdSkipPassInfoViewModel)
        {
            if (heldAdSkipPassInfoViewModel.IsEmpty())
            {
                SetUpQuickAdReceiveButton(intervalTime);
            }
            else
            {
                SetUpQuickAdSkipReceiveButton(intervalTime, heldAdSkipPassInfoViewModel.PassProductName);
            }
        }

        void SetUpQuickAdReceiveButton(string intervalTime)
        {
            _adButtonObject.Hidden = false;
            _adSkipButtonObject.Hidden = true;
            _quickAdReceiveIntervalText.SetText(intervalTime);
            _adButton.interactable = _quickAdIntervalTextObject.gameObject.activeSelf && string.IsNullOrEmpty(intervalTime);
            _adButtonTextObj.SetActive(_adButton.interactable || !_quickAdIntervalTextObject.gameObject.activeSelf);
        }

        void SetUpQuickAdSkipReceiveButton(string intervalTime, PassProductName passProductName)
        {
            _adButtonObject.Hidden = true;
            _adSkipButtonObject.Hidden = false;

            _quickAdSkipReceiveIntervalText.SetText(intervalTime);

            _adSkipButton.interactable = _quickAdSkipIntervalTextObject.gameObject.activeSelf && string.IsNullOrEmpty(intervalTime);
            _adButtonTextObj.SetActive(_adSkipButton.interactable || !_quickAdSkipIntervalTextObject.gameObject.activeSelf);

            _adSkipButton.TitleText.SetText(ZString.Format("{0}適用中", passProductName.ToString()));
        }
    }
}
