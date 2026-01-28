using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Shop;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Shop;
using Zenject;

namespace GLOW.Scenes.DiamondPurchaseHistory.Domain
{
    public class DiamondPurchaseHistoryUseCase
    {
        [Inject] IShopService ShopService { get; }

        public async UniTask<DiamondPurchaseHistoryUseCaseModel> GetModel(CancellationToken cancellationToken)
        {
            var model = await ShopService.PurchaseHistory(cancellationToken);
            var elements = model.CurrencyPurchaseModels.Select(CreateModel).ToList();
            return new DiamondPurchaseHistoryUseCaseModel(elements);
        }

        DiamondPurchaseHistoryElementUseCaseModel CreateModel(CurrencyPurchaseModel model)
        {
            return new DiamondPurchaseHistoryElementUseCaseModel(
                model.PurchasePrice,
                model.PurchasedAmount,
                CreateProductName(model.PurchasedAmount),
                model.PurchaseAt
            );
        }

        ProductName CreateProductName(PaidDiamond amount)
        {
            var format = "有償プリズム {0}個";
            return new ProductName(ZString.Format(format, amount.ToStringSeparated()) );
        }
    }
}
