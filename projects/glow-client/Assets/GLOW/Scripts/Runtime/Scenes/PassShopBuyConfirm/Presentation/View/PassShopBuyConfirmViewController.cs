using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PassShopBuyConfirm.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PassShopBuyConfirm.Presentation.View
{
    public class PassShopBuyConfirmViewController : UIViewController<PassShopBuyConfirmView>
    {
        public record Argument(MasterDataId MstShopPassId);
        public Action OnOkSelected { get; set; }
        [Inject] IPassShopBuyConfirmViewDelegate ViewDelegate { get; }
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            
            ViewDelegate.OnViewDidLoad();
        }
        
        public void SetUpViewUi(PassShopBuyConfirmViewModel viewModel)
        {
            ActualView.SetupPassIcon(viewModel.PassIconAssetPath);
            ActualView.SetPassName(viewModel.PassProductName);
            ActualView.SetPassPrice(viewModel.RawProductPriceText);

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
        
        [UIAction]
        void OnBuySelected()
        {
            ViewDelegate.OnBuySelected();
        }
        
        [UIAction]
        void OnSpecificCommerceButtonSelected()
        {
            ViewDelegate.ShowSpecificCommerce();
        }

        [UIAction]
        void OnFundsSettlementButtonSelected()
        {
            ViewDelegate.ShowFundsSettlement();
        }
    }
}