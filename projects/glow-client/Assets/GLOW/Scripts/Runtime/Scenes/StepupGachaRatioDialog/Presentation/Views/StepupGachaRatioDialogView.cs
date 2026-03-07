using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using GLOW.Scenes.GachaRatio.Presentation.Views.Components;
using GLOW.Scenes.StepupGachaRatioDialog.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.StepupGachaRatioDialog.Presentation.Views
{
    public class StepupGachaRatioDialogView : UIView
    {
        [Header("ステップタブ")]
        [SerializeField] UIObject _stepTab2Object;
        [SerializeField] UIObject _stepTab3Object;
        [SerializeField] UIObject _stepTab4Object;
        [SerializeField] UIObject _stepTab5Object;
        
        [Header("確定枠タブ")]
        [SerializeField] UIObject _ssrTabObject;
        [SerializeField] UIText _ssrTabOnText;
        [SerializeField] UIText _ssrTabOffText;
        
        [Header("タブ・提供割合コンポーネント")]
        [SerializeField] GachaRatioPageRootComponent _gachaRatioPageRootComponent;
        [SerializeField] UIToggleableComponentGroup _stepButtonGroup;
        [SerializeField] UIToggleableComponentGroup _prizeTypeTabButtonGroup;
        [SerializeField] ScrollRect _scrollRect;

        IReadOnlyList<StepupGachaRatioStepViewModel> _stepViewModels = new List<StepupGachaRatioStepViewModel>();

        public ScrollRect ScrollRect => _scrollRect;

        public void Setup(StepupGachaRatioDialogViewModel viewModel)
        {
            _stepViewModels = viewModel.StepViewModels;
            SetupStepTabVisibility(_stepViewModels.Count);
        }

        public void SwitchStepRatioPage(StepupGachaRatioStepViewModel stepViewModel)
        {
            SetupPageComponents(stepViewModel);
            UpdateSsrTab(stepViewModel);
            _stepButtonGroup.SetToggleOn(GetStepToggleButtonKey(stepViewModel.StepNumber.Value));
            _gachaRatioPageRootComponent.SwitchGachaRatioPage(GachaRatioTabType.NormalRatioTab);
            _prizeTypeTabButtonGroup.SetToggleOn(GetTabToggleButtonKey(GachaRatioTabType.NormalRatioTab));
            MoveScrollToTargetPos(1);
        }

        public void SwitchPrizeTypeTab(bool isNormalPrize)
        {
            var tabType = isNormalPrize ? GachaRatioTabType.NormalRatioTab : GachaRatioTabType.SSRRatioTab;
            _gachaRatioPageRootComponent.SwitchGachaRatioPage(tabType);
            _prizeTypeTabButtonGroup.SetToggleOn(GetTabToggleButtonKey(tabType));
            MoveScrollToTargetPos(1);
        }

        public void MoveScrollToTargetPos(float targetPos)
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollRect.content);
            _scrollRect.verticalNormalizedPosition = targetPos;
        }

        public StepupGachaRatioStepViewModel GetStepViewModelByIndex(int stepIndex)
        {
            if (stepIndex < 0 || stepIndex >= _stepViewModels.Count) return null;

            return _stepViewModels[stepIndex];
        }
        
        void UpdateSsrTab(StepupGachaRatioStepViewModel stepViewModel)
        {
            var isFixedPrizeEmpty = stepViewModel.FixedPrizePageViewModel.IsEmpty();
            _ssrTabObject.Hidden = isFixedPrizeEmpty;

            if (!isFixedPrizeEmpty)
            {
                _ssrTabOnText.SetText(stepViewModel.GachaFixedPrizeDescription.Value);
                _ssrTabOffText.SetText(stepViewModel.GachaFixedPrizeDescription.Value);
            }
        }

        void SetupStepTabVisibility(int stepCount)
        {
            _stepTab2Object.Hidden = stepCount < 2;
            _stepTab3Object.Hidden = stepCount < 3;
            _stepTab4Object.Hidden = stepCount < 4;
            _stepTab5Object.Hidden = stepCount < 5;
        }

        void SetupPageComponents(StepupGachaRatioStepViewModel stepViewModel)
        {
            var dialogViewModel = new GachaRatioDialogViewModel(
                stepViewModel.NormalPrizePageViewModel,
                stepViewModel.FixedPrizePageViewModel,
                GachaRatioPageViewModel.Empty,
                GachaRatioPageViewModel.Empty,
                GachaFixedPrizeDescription.Empty);

            _gachaRatioPageRootComponent.Setup(dialogViewModel);
        }

        string GetStepToggleButtonKey(int stepNumber)
        {
            return ZString.Format("Step{0}", stepNumber);
        }

        string GetTabToggleButtonKey(GachaRatioTabType type)
        {
            switch (type)
            {
                case GachaRatioTabType.NormalRatioTab:
                    return "Normal";
                case GachaRatioTabType.SSRRatioTab:
                    return "SSR";
                case GachaRatioTabType.URRatioTab:
                case GachaRatioTabType.PickupRatioTab:
                default:
                    // ステップアップガチャの提供割合は通常枠と確定枠のみのため、URやピックアップのタブは存在しない。
                    return "Normal";
            }
        }
    }
}
