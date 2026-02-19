using System.Collections.Generic;
using GLOW.Scenes.PackShop.Domain.Models;

namespace GLOW.Scenes.PackShop.Domain.Calculator
{
    public interface IPackShopProductEvaluator
    {
        public PackProductEvaluateModel GetValidateProductList();
    }
}
