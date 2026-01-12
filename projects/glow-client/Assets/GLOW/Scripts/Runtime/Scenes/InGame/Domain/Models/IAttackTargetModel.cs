using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public interface IAttackTargetModel
    {
        FieldObjectId Id { get; }
        BattleSide BattleSide { get; }
        HP MaxHp { get; }
        HP Hp { get; }
        OutpostCoordV2 Pos { get; }
        TickCount PosUpdateStageTickCount { get; }
        FieldObjectType FieldObjectType { get; }
        CharacterColor Color { get; }
        CharacterUnitRoleType RoleType { get; }
        MasterDataId MstSeriesId { get; }
        MasterDataId CharacterId { get; }
        AttackTargetOrder AttackTargetOrder { get; }
    }
}
