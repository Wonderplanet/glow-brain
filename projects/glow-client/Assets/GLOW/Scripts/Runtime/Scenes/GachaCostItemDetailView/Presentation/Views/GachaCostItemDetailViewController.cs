using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaCostItemDetailView.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using UIKit;
using Zenject;

namespace GLOW.Scenes.GachaCostItemDetailView.Presentation.Views
{
    /// <summary>
    /// 81_アイテムBOXリスト
    /// 　81-3_アイテムBOXページダイアログ
    /// 　　81-3-6_ガシャチケット詳細画面
    /// </summary>
    public class GachaCostItemDetailViewController : UIViewController<GachaCostItemDetailView>
    {
        public record Argument(MasterDataId MstCostId, ShowTransitAreaFlag ShowTransitAreaFlag);

        [Inject] IGachaCostItemDetailViewDelegate ViewDelegate { get; }

        bool _isGrayOutTransitionButton;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetViewModel(GachaCostItemDetailViewModel viewModel)
        {
            ActualView.SetUpPlayerResourceIconComponent(viewModel.IconViewModel);
            ActualView.SetNameText(viewModel.Name);
            ActualView.SetAmountText(viewModel.IconViewModel.Amount);
            ActualView.SetDescriptionText(viewModel.Description);
            ActualView.SetTransitionAreaVisible(viewModel.IsTransitAreaVisible);
            ActualView.SetTransitionButtonGrayout(viewModel.IsTransitionButtonGrayOut);
        }
        
        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
        
        [UIAction]
        void OnTransitionButtonTapped()
        {
            ViewDelegate.OnTransitionButtonTapped();
            
            if (_isGrayOutTransitionButton)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
            }
            else
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }
        }
    }
}