using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Scenes.Shop.Domain.UseCase;

namespace GLOW.Scenes.Shop.Presentation.Presenter
{
    public class ShopPresentationExecSupport
    {
        BuyShopProductUseCase _buyShopProductUseCase;


        public ShopPresentationExecSupport(BuyShopProductUseCase buyShopProductUseCase)
        {
            _buyShopProductUseCase = buyShopProductUseCase;
        }

        public async UniTask<IReadOnlyList<CommonReceiveResourceViewModel>> BuyProduct(
            CancellationToken ct,
            MasterDataId productId)
        {
            var model = await _buyShopProductUseCase.BuyProduct(ct, productId);

            return new List<CommonReceiveResourceViewModel>()
            {
                CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(model)
            };
        }
    }
}
