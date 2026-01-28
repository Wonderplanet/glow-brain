using System.Collections.Generic;

namespace GLOW.Scenes.Shop.Domain.Calculator
{
    public interface ILimitAmountModelCalculator
    {
        public IReadOnlyList<LimitCheckModel> FilteringLimitAmount(IReadOnlyList<LimitCheckModel> models);
    }
}
