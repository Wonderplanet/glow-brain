using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.IdleIncentive;
using GLOW.Scenes.IdleIncentiveTop.Domain.ValueObjects;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels
{
    public record IdleIncentiveTopViewModel(
        IdleIncentiveRewardAmount OneHourCoinReward,
        IdleIncentiveRewardAmount PassEffectCoinReward,
        IdleIncentiveRewardAmount OneHourExpReward,
        IdleIncentiveRewardAmount PassEffectExpReward,
        EnableQuickReceiveFlag EnableQuickReward,
        string MaxIdleIncentiveHour,
        IReadOnlyList<HeldPassEffectDisplayViewModel> HeldPassEffectDisplayViewModels)
    {
        public string ToDisplayAmountPerHour(IdleIncentiveRewardAmount rewardAmount, int rewardFractionalFontSize)
        {
            var formattedValue = rewardAmount.GetCalculatedAmountString();
            (var integerPart, var fractionalPart) = SplitIntegerAndFractionalParts(formattedValue);

            // 小数点以下は文字サイズを小さくする
            return fractionalPart.Length > 1
                ? ZString.Format("{0}<size={1}>{2}</size>", integerPart, rewardFractionalFontSize, fractionalPart)
                : integerPart;
        }

        (string integerPart, string fractionalPart) SplitIntegerAndFractionalParts(string formattedValue)
        {
            var dotIndex = formattedValue.IndexOf('.');

            if (dotIndex == -1)
            {
                return (formattedValue, string.Empty);
            }

            var integerPart = formattedValue.Substring(0, dotIndex);
            var fractionalPart = formattedValue.Substring(dotIndex).TrimEnd('0').TrimEnd('.');

            return (integerPart, fractionalPart);
        }
    };
}
