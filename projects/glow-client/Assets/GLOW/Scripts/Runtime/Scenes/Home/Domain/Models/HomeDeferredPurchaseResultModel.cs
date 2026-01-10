using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Shop;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeDeferredPurchaseResultModel(
        IReadOnlyList<HomeDeferredPurchaseProductResultModel> ProductResults,
        IReadOnlyList<DeferredPurchaseErrorCode> ErrorCodes)
    {
        public static HomeDeferredPurchaseResultModel Empty { get; } = new(
            new List<HomeDeferredPurchaseProductResultModel>(),
            new List<DeferredPurchaseErrorCode>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
