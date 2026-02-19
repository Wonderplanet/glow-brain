using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaRatio.Domain.Constants;
using GLOW.Scenes.GachaRatio.Presentation.Views.Components;
using GLOW.Scenes.GachaRatio.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaRatio.Presentation.Views
{
    /// <summary>
    /// 71-1_ガシャ
    /// 　71-1-12_提供割合表示(BNEレギュ)
    /// </summary>
    public class GachaRatioDialogView : UIView
    {
        [SerializeField] GachaRatioPageRootComponent _gachaRatioPageRootComponent;
        [SerializeField] UIToggleableComponentGroup _tabButtonGroup;

        [SerializeField] UIObject _ssrTabButton;
        [SerializeField] UIObject _urTabButton;
        [SerializeField] UIObject _pickupTabButton;
        [SerializeField] UIText _fixedPrizeTabOnText;
        [SerializeField] UIText _fixedPrizeTabOffText;

        [SerializeField] ScrollRect _scrollRect;
        public ScrollRect ScrollRect => _scrollRect;
        public void Setup(GachaRatioDialogViewModel viewModel)
        {
            _gachaRatioPageRootComponent.Setup(viewModel);
            SetupTabButtonGroup(viewModel);
        }

        public void GachaRatioPageUpdate(GachaRatioTabType type)
        {
            _gachaRatioPageRootComponent.SwitchGachaRatioPage(type);
            _tabButtonGroup.SetToggleOn(GetTabToggleButtonKey(type));
            // スクロールを一番上に戻す
            MoveScrollToTargetPos(1);
        }

        public void MoveScrollToTargetPos(float targetPos)
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollRect.content);
            _scrollRect.verticalNormalizedPosition = targetPos;
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
                    return "UR";
                case GachaRatioTabType.PickupRatioTab:
                    return "Pickup";
                default:
                    return "Normal";
            }
        }
        
        void SetupTabButtonGroup(GachaRatioDialogViewModel viewModel)
        {
            // 通常タブのみの場合（他のタブが空の場合）はタブを非表示にする
            if (viewModel.PickupRatioPageViewModel.IsEmpty() &&
                viewModel.SSRRatioPageViewModel.IsEmpty() &&
                viewModel.URRatioPageViewModel.IsEmpty())
            {
                _tabButtonGroup.Hidden = true;
                return;
            }
            
            if (!viewModel.GachaFixedPrizeDescription.IsEmpty())
            {
                // 10連確定枠のタブテキストを設定
                _fixedPrizeTabOnText.SetText(viewModel.GachaFixedPrizeDescription.Value);
                _fixedPrizeTabOffText.SetText(viewModel.GachaFixedPrizeDescription.Value);
            }

            _ssrTabButton.Hidden = viewModel.SSRRatioPageViewModel.IsEmpty();
            _urTabButton.Hidden = viewModel.URRatioPageViewModel.IsEmpty();
            _pickupTabButton.Hidden = viewModel.PickupRatioPageViewModel.IsEmpty();
        }
    }
}
