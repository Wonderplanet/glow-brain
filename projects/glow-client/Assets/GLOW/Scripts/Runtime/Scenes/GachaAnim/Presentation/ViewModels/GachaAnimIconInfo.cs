using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.GachaAnim.Presentation.ViewModels
{
    public record GachaAnimIconInfo(Rarity Rarity, ResourceType ResourceType)
    {
        public static GachaAnimIconInfo CreateGachaAnimIconInfo(Rarity rarity, ResourceType resourceType)
        {
            if (resourceType == ResourceType.Unit)
            {
                return new GachaAnimIconInfo(rarity, resourceType);
            }

            // ユニット以外は最低レアリティで返す
            return new GachaAnimIconInfo(Rarity.R, resourceType);
        }
    }
}
