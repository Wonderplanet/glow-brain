#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Debugs.InGame.Domain.Models
{
    // スキル操作用のユニット情報
    public record DebugSkillOperationUnitInfoModel(
        MasterDataId CharacterId,
        BattleSide BattleSide,
        CharacterName CharacterName)
    {
        public static DebugSkillOperationUnitInfoModel Empty { get; } = new (
            MasterDataId.Empty,
            BattleSide.Player,
            CharacterName.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
#endif


