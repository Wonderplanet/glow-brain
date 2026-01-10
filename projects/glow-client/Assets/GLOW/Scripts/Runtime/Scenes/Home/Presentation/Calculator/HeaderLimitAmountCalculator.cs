using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.Calculator
{
    public static class HeaderLimitAmountCalculator
    {

        public static bool IsLimitAmount(
            int checkedNumber, int checkingAfterNumber,
            ILimitedAmountValueObject before, ILimitedAmountValueObject after,
            int limitAmountValue)
        {
            if(before == null && after == null) return false;
            if (before == null) return limitAmountValue < after.HasAmount;

            // Diffあるかチェック
            if (checkedNumber == checkingAfterNumber) return false;
            // 前回が上限以下、かつ今回上限突破したとき
            if(before.HasAmount <= limitAmountValue && limitAmountValue < after.HasAmount) return true;

            return false;
        }
    }
}
