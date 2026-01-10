using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonReceiveView.Presentation.ViewModel;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Shop.Domain.UseCase;
using Zenject;

namespace GLOW.Scenes.Shop.Presentation.Presenter
{
    public class ShopPurchasePresentationHandler
    {
        [Inject] BuyStoreProductUseCase BuyStoreProductUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }

        public async UniTask PurchaseStoreProduct(
            CancellationToken ct,
            MasterDataId productId)
        {
            var boughtProducts = await BuyStoreProductUseCase.BuyStoreProduct(ct, productId);

            var viewModels = boughtProducts
                .Select(product => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(product))
                .ToList();

            CommonReceiveWireFrame.Show(viewModels);
        }
    }
}
