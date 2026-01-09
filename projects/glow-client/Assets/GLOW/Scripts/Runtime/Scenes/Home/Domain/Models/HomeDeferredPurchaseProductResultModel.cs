using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Shop;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeDeferredPurchaseProductResultModel(
        ProductType ProductType,
        ProductName ProductName,
        IReadOnlyList<CommonReceiveResourceModel> CommonReceiveResourceModels)
    {
        public static HomeDeferredPurchaseProductResultModel Empty { get; } = new(
            ProductType.Diamond,
            ProductName.Empty,
            new List<CommonReceiveResourceModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
