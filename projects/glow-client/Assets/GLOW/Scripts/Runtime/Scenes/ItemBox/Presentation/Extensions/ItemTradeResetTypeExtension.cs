using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.ItemBox.Presentation.Extensions
{
    public static class ItemTradeResetTypeExtension
    {
        public static string ToDisplayString(this ItemTradeResetType resetType)
        {
            return resetType switch
            {
                ItemTradeResetType.Daily => "(毎日更新)",
                ItemTradeResetType.Weekly => "(毎週更新)",
                ItemTradeResetType.Monthly => "(毎月更新)",
                _ => string.Empty
            };
        }
    }
}