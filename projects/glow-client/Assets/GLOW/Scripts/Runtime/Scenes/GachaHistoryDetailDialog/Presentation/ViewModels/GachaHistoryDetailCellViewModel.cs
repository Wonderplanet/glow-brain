using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels
{
    public record GachaHistoryDetailCellViewModel(
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        PlayerResourceName ResourceName,
        CharacterName CharacterName,
        PlayerResourceIconAssetPath AcquiredPlayerResourceIconAssetPath,
        PlayerResourceAmount AcquiredPlayerResourceAmount)
    {
        public bool IsAcquiredUnit()
        {
            // ユニットで、アイコンパスが空でない場合に獲得済みとする
            return PlayerResourceIconViewModel.ResourceType == ResourceType.Unit &&
                   !AcquiredPlayerResourceIconAssetPath.IsEmpty();
        }
        
        public string GetDisplayName()
        {
            // ユニットの場合はキャラ名を表示
            if (PlayerResourceIconViewModel.ResourceType == ResourceType.Unit &&
                !CharacterName.IsEmpty())
            {
                return CharacterName.Value;
            }

            return ResourceName.Value;
        }
        
        public bool ShouldShowAmountObject()
        {
            return !AcquiredPlayerResourceIconAssetPath.IsEmpty();
        }
    }
}