using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record DefenseTargetModel(
        FieldObjectId Id,
        DefenseTargetAssetKey AssetKey,
        OutpostCoordV2 Pos,
        HP MaxHp,
        HP Hp) : IAttackTargetModel
    {
        public static DefenseTargetModel Empty { get; } = new (
            FieldObjectId.Empty,
            DefenseTargetAssetKey.Empty,
            OutpostCoordV2.Empty,
            HP.Empty,
            HP.Empty);

        public BattleSide BattleSide => BattleSide.Player;

        public FieldObjectType FieldObjectType => FieldObjectType.DefenseTarget;
        public AttackTargetOrder AttackTargetOrder => AttackTargetOrder.Outpost; // ゲートと同じ
        public CoordinateRange BoundingRange => CoordinateRange.Zero;
        public CharacterColor Color => CharacterColor.None;
        public CharacterUnitRoleType RoleType => CharacterUnitRoleType.None;
        public TickCount PosUpdateStageTickCount => TickCount.Zero;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsHpZero()
        {
            return Hp <= HP.Zero;
        }
    }
}
