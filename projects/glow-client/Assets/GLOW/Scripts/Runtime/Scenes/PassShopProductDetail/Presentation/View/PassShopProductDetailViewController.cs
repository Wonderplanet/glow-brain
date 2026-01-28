using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShopProductDetail.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.View
{
    public class PassShopProductDetailViewController : UIViewController<PassShopProductDetailView>
    {
        public record Argument(MasterDataId MstShopPassId);
        [Inject] IPassShopProductDetailViewDelegate ViewDelegate { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            
            ViewDelegate.OnViewDidLoad();
        }
        
        public void SetUpViewUi(PassShopProductDetailViewModel viewModel)
        {
            ActualView.SetupPassIcon(viewModel.PassIconAssetPath);
            ActualView.SetPassName(viewModel.PassProductName);
            ActualView.SetPassProductDescription(
                viewModel.PassProductName,
                viewModel.PassStartAt,
                viewModel.PassEndAt,
                viewModel.IsDisplayExpiration,
                viewModel.PassDurationDay,
                viewModel.PassEffectViewModels,
                viewModel.PassReceivableMaxRewardViewModels);
            
            foreach (var passEffectViewModel in viewModel.PassEffectViewModels)
            {
                var passEffectCell = ActualView.InstantiatePassEffectCell();
                passEffectCell.SetupEffectCell(
                    passEffectViewModel.PassEffectType,
                    passEffectViewModel.PassEffectValue);
            }
            ActualView.SetPassEffectSectionTitleVisible(!viewModel.PassEffectViewModels.IsEmpty());
            
            foreach (var passReceivableRewardViewModel in viewModel.PassReceivableMaxRewardViewModels)
            {
                var passReceivableRewardCell = ActualView.InstantiateProductListCell();
                passReceivableRewardCell.Setup(passReceivableRewardViewModel);
            }
            ActualView.SetPassRewardSectionTitleVisible(!viewModel.PassReceivableMaxRewardViewModels.IsEmpty());
        }
        
        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}