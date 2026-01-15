using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    /// <summary>
    /// 拠点
    /// </summary>
    public record OutpostModel(
        FieldObjectId Id,
        StateEffectSourceId StateEffectSourceId,
        BattleSide BattleSide,
        ArtworkAssetPath ArtworkAssetPath,
        OutpostAssetKey OutpostAssetKey,
        HP MaxHp,
        HP Hp,
        OutpostDamageInvalidationFlag DamageInvalidationFlag,
        OutpostCoordV2 Pos,
        OutpostHpSpecialRuleFlag OutpostHpSpecialRuleFlag) : IAttackTargetModel
    {
        public static OutpostModel Empty { get; } = new(
            FieldObjectId.Empty,
            StateEffectSourceId.Empty,
            BattleSide.Player,
            ArtworkAssetPath.Empty,
            OutpostAssetKey.Empty,
            HP.Empty,
            HP.Empty,
            OutpostDamageInvalidationFlag.False,
            OutpostCoordV2.Empty,
            OutpostHpSpecialRuleFlag.False);

        public FieldObjectType FieldObjectType => FieldObjectType.Outpost;
        public AttackTargetOrder AttackTargetOrder => AttackTargetOrder.Outpost;
        public CoordinateRange BoundingRange => CoordinateRange.Zero;
        public CharacterColor Color => CharacterColor.None;
        public CharacterUnitRoleType RoleType => CharacterUnitRoleType.None;
        public MasterDataId MstSeriesId => MasterDataId.Empty;
        public MasterDataId CharacterId => MasterDataId.Empty;
        public TickCount PosUpdateStageTickCount => TickCount.Zero;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
