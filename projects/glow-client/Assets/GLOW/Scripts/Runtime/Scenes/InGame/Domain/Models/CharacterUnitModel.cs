using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record CharacterUnitModel(
        FieldObjectId Id,
        StateEffectSourceId StateEffectSourceId,
        AutoPlayerSequenceElementId AutoPlayerSequenceElementId,
        FieldObjectType FieldObjectType,
        BattleSide BattleSide,
        CharacterUnitKind Kind,
        Rarity Rarity,
        CharacterUnitRoleType RoleType,
        CharacterColor Color,
        MasterDataId CharacterId,
        MasterDataId MstSeriesId,
        MasterDataId StageParameterId,
        UnitAssetKey AssetKey,
        InitialCharacterUnitCoef InitialCoef,
        HP MaxHp,
        HP Hp,
        KnockBackCount DamageKnockBackCount,
        UnitMoveSpeed UnitMoveSpeed,
        WellDistance WellDistance,
        AttackPower AttackPower,
        HealPower HealPower,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        CharacterColorAdvantageDefenseBonus ColorAdvantageDefenseBonus,
        AttackData NormalAttack,
        AttackData SpecialAttack,
        AttackData AppearanceAttack,
        AttackComboCycle AttackComboCycle,
        AttackComboCount AttackComboCount,
        TickCount RemainingAttackInterval,
        AttackKind NextAttackKind,
        List<UnitAbilityModel> Abilities,
        ICharacterUnitAction Action,
        UnitActionState PrevActionState,
        MarchingLaneIdentifier MarchingLane,
        OutpostCoordV2 Pos,
        OutpostCoordV2 PrevPos,
        ICommonConditionModel MoveStartCondition,
        ICommonConditionModel MoveStopCondition,
        ICommonConditionModel MoveRestartCondition,
        MoveLoopCount RemainingMoveLoopCount,
        KomaModel LocatedKoma, // 現在いるコマのKomaModel
        KomaModel PrevLocatedKoma, // 一つ前のフレームでいたコマのKomaModel
        KomaModel MoveStartedKoma, // 移動を開始した時点のコマのKomaModel
        TickCount PosUpdateStageTickCount, // 位置を更新したステージ時刻
        TickCount MoveStopStageTickCount, // 移動を停止したステージ時刻
        TickCount MoveStartStageTickCount, // 移動を開始したステージ時刻
        IReadOnlyList<IStateEffectModel> StateEffects,
        TickCount SummonedTickCount, // 生成されたInGameScene.CurrentTickCountのタイミング(SummoningActionは加味しない)
        SummonAnimationType SummonAnimationType,
        IReadOnlyList<UnitSpeechBalloonModel> SpeechBalloons,
        UnitTransformationModel Transformation,
        DropBattlePoint DropBattlePoint,
        bool IsMoveStarted, //PrevMoveから動き出したか
        MoveStoppedFlag IsMoveStopped, // 移動停止条件を満たして移動停止状態になっているか
        MoveStoppedFlag IsPrevMoveStopped, // 一つ前のフレームで移動停止条件を満たして移動停止状態になっていたか
        DefeatTargetFlag IsDefeatTarget,
        OutpostDamageInvalidationFlag IsOutpostDamageInvalidation, // このユニットが生存時に自陣側ゲートのダメージ無効化させるか
        PhantomizedFlag Phantomized,
        UnitAuraType AuraType,
        UnitDeathType DeathType,
        InGameScore DefeatedScore) : IAttackTargetModel
    {
        public static CharacterUnitModel Empty { get; } = new(
            FieldObjectId.Empty,
            StateEffectSourceId.Empty,
            AutoPlayerSequenceElementId.Empty,
            FieldObjectType.None,
            BattleSide.Player,
            CharacterUnitKind.Normal,
            Rarity.R,
            CharacterUnitRoleType.Attack,
            CharacterColor.Colorless,
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            UnitAssetKey.Empty,
            InitialCharacterUnitCoef.Empty,
            HP.Empty,
            HP.Empty,
            new KnockBackCount(0),
            new UnitMoveSpeed(0),
            WellDistance.Empty,
            AttackPower.Empty,
            HealPower.Empty,
            CharacterColorAdvantageAttackBonus.Empty,
            CharacterColorAdvantageDefenseBonus.Empty,
            AttackData.Empty,
            AttackData.Empty,
            AttackData.Empty,
            AttackComboCycle.Empty,
            new AttackComboCount(0),
            TickCount.Empty,
            AttackKind.Normal,
            new List<UnitAbilityModel>(),
            new CharacterUnitMoveAction(),
            UnitActionState.Summoning,
            MarchingLaneIdentifier.Empty,
            OutpostCoordV2.Empty,
            OutpostCoordV2.Empty,
            EmptyCommonConditionModel.Instance,
            EmptyCommonConditionModel.Instance,
            EmptyCommonConditionModel.Instance,
            MoveLoopCount.Empty,
            KomaModel.Empty,
            KomaModel.Empty,
            KomaModel.Empty,
            TickCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            new List<IStateEffectModel>(),
            TickCount.Empty,
            SummonAnimationType.None,
            new List<UnitSpeechBalloonModel>(),
            UnitTransformationModel.Empty,
            DropBattlePoint.Empty,
            false,
            MoveStoppedFlag.False,
            MoveStoppedFlag.False,
            DefeatTargetFlag.False,
            OutpostDamageInvalidationFlag.False,
            PhantomizedFlag.False,
            UnitAuraType.Default,
            UnitDeathType.Normal,
            InGameScore.Empty);

        public AttackTargetOrder AttackTargetOrder => AttackTargetOrder.Unit;
        public bool IsDead => Hp.IsZero();
        public bool IsVanished => !IsDead && Transformation.IsTransformationFinish;
        public bool IsBoss => Kind is CharacterUnitKind.Boss or CharacterUnitKind.AdventBattleBoss;

        public bool ShouldAttachKomaEffect => Action.ActionState != UnitActionState.Summoning;

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsStateEnd(UnitActionState state)
        {
            return PrevActionState == state && Action.ActionState != state;
        }

        public bool IsStateStart(UnitActionState state)
        {
            return PrevActionState != state && Action.ActionState == state;
        }

        /// <summary> 移動停止から移動に切り替わったタイミング </summary>
        public bool IsStopToMoveStart()
        {
            return Action.ActionState == UnitActionState.Move && IsPrevMoveStopped && !IsMoveStopped;
        }

        /// <summary> 移動から移動停止に切り替わったタイミング </summary>
        public bool IsMoveStartToStop()
        {
            return Action.ActionState == UnitActionState.Move && !IsPrevMoveStopped && IsMoveStopped;
        }

        public AttackData GetAttackData(AttackComboCycle comboCycle, AttackComboCount comboCount)
        {
            if (SpecialAttack.IsEmpty()) return NormalAttack;

            return comboCount.IsSpecialAttack(comboCycle) ? SpecialAttack : NormalAttack;
        }

        public AttackKind GetNextNextComboAttackKind()
        {
            if (SpecialAttack.IsEmpty()) return AttackKind.Normal;
            if (AttackComboCycle.IsEmpty()) return AttackKind.Normal;

            var nextComboCount = AttackComboCount.NextComboCount(AttackComboCycle);
            var nextNextComboCount = nextComboCount.NextComboCount(AttackComboCycle);
            return nextNextComboCount.IsSpecialAttack(AttackComboCycle) ? AttackKind.Special : AttackKind.Normal;
        }

        public bool IsSpecialAttack(AttackComboCycle comboCycle, AttackComboCount comboCount)
        {
            return SpecialAttack.IsEmpty() ? false : comboCount.IsSpecialAttack(comboCycle);
        }

        public bool IsSpecialAttackReady()
        {
            return NextAttackKind == AttackKind.Special
                   && Action.ActionState != UnitActionState.AttackCharge
                   && Action.ActionState != UnitActionState.SpecialAttack;
        }

        public UndeadFlag IsUndead()
        {
            // 自身のHPが条件で変身するキャラは撃破されない
            return new UndeadFlag(Transformation.Condition.ConditionType == InGameCommonConditionType.MyHpLessThanOrEqualPercentage);
        }

        public virtual bool Equals(CharacterUnitModel other)
        {
            if (ReferenceEquals(this, other)) return true;
            if (other == null) return false;

            if (Id != other.Id) return false;
            if (StateEffectSourceId != other.StateEffectSourceId) return false;
            if (AutoPlayerSequenceElementId != other.AutoPlayerSequenceElementId) return false;
            if (FieldObjectType != other.FieldObjectType) return false;
            if (BattleSide != other.BattleSide) return false;
            if (Kind != other.Kind) return false;
            if (Rarity != other.Rarity) return false;
            if (RoleType != other.RoleType) return false;
            if (Color != other.Color) return false;
            if (CharacterId != other.CharacterId) return false;
            if (StageParameterId != other.StageParameterId) return false;
            if (AssetKey != other.AssetKey) return false;
            if (InitialCoef != other.InitialCoef) return false;
            if (MaxHp != other.MaxHp) return false;
            if (Hp != other.Hp) return false;
            if (DamageKnockBackCount != other.DamageKnockBackCount) return false;
            if (UnitMoveSpeed != other.UnitMoveSpeed) return false;
            if (WellDistance != other.WellDistance) return false;
            if (AttackPower != other.AttackPower) return false;
            if (HealPower != other.HealPower) return false;
            if (ColorAdvantageAttackBonus != other.ColorAdvantageAttackBonus) return false;
            if (ColorAdvantageDefenseBonus != other.ColorAdvantageDefenseBonus) return false;
            if (NormalAttack != other.NormalAttack) return false;
            if (SpecialAttack != other.SpecialAttack) return false;
            if (AppearanceAttack != other.AppearanceAttack) return false;
            if (AttackComboCycle != other.AttackComboCycle) return false;
            if (AttackComboCount != other.AttackComboCount) return false;
            if (RemainingAttackInterval != other.RemainingAttackInterval) return false;
            if (NextAttackKind != other.NextAttackKind) return false;
            if (!Equals(Action, other.Action)) return false;
            if (PrevActionState != other.PrevActionState) return false;
            if (MarchingLane != other.MarchingLane) return false;
            if (Pos != other.Pos) return false;
            if (PrevPos != other.PrevPos) return false;
            if (!Equals(MoveStartCondition, other.MoveStartCondition)) return false;
            if (!Equals(MoveStopCondition, other.MoveStopCondition)) return false;
            if (!Equals(MoveRestartCondition, other.MoveRestartCondition)) return false;
            if (RemainingMoveLoopCount != other.RemainingMoveLoopCount) return false;
            if (!Equals(LocatedKoma, other.LocatedKoma)) return false;
            if (!Equals(PrevLocatedKoma, other.PrevLocatedKoma)) return false;
            if (!Equals(MoveStartedKoma, other.MoveStartedKoma)) return false;
            if (PosUpdateStageTickCount != other.PosUpdateStageTickCount) return false;
            if (MoveStopStageTickCount != other.MoveStopStageTickCount) return false;
            if (MoveStartStageTickCount != other.MoveStartStageTickCount) return false;
            if (SummonedTickCount != other.SummonedTickCount) return false;
            if (SummonAnimationType != other.SummonAnimationType) return false;
            if (Transformation != other.Transformation) return false;
            if (DropBattlePoint != other.DropBattlePoint) return false;
            if (IsMoveStarted != other.IsMoveStarted) return false;
            if (IsMoveStopped != other.IsMoveStopped) return false;
            if (IsPrevMoveStopped != other.IsPrevMoveStopped) return false;
            if (IsOutpostDamageInvalidation != other.IsOutpostDamageInvalidation) return false;
            if (Phantomized != other.Phantomized) return false;
            if (AuraType != other.AuraType) return false;
            if (DeathType != other.DeathType) return false;
            if (DefeatedScore != other.DefeatedScore) return false;

            if ((Abilities == null) ^ (other.Abilities == null)) return false;
            if (Abilities != null && other.Abilities != null)
            {
                if (!Abilities.SequenceEqual(other.Abilities)) return false;
            }

            if ((StateEffects == null) ^ (other.StateEffects == null)) return false;
            if (StateEffects != null && other.StateEffects != null)
            {
                if (!StateEffects.SequenceEqual(other.StateEffects)) return false;
            }

            if ((SpeechBalloons == null) ^ (other.SpeechBalloons == null)) return false;
            if (SpeechBalloons != null && other.SpeechBalloons != null)
            {
                if (!SpeechBalloons.SequenceEqual(other.SpeechBalloons)) return false;
            }

            return true;
        }

        public override int GetHashCode()
        {
            HashCode hash = new();

            hash.Add(Id);
            hash.Add(StateEffectSourceId);
            hash.Add(AutoPlayerSequenceElementId);
            hash.Add(FieldObjectType);
            hash.Add(BattleSide);
            hash.Add(Kind);
            hash.Add(Rarity);
            hash.Add(RoleType);
            hash.Add(Color);
            hash.Add(CharacterId);
            hash.Add(StageParameterId);
            hash.Add(AssetKey);
            hash.Add(InitialCoef);
            hash.Add(MaxHp);
            hash.Add(Hp);
            hash.Add(DamageKnockBackCount);
            hash.Add(UnitMoveSpeed);
            hash.Add(WellDistance);
            hash.Add(AttackPower);
            hash.Add(HealPower);
            hash.Add(ColorAdvantageAttackBonus);
            hash.Add(ColorAdvantageDefenseBonus);
            hash.Add(NormalAttack);
            hash.Add(SpecialAttack);
            hash.Add(AppearanceAttack);
            hash.Add(AttackComboCycle);
            hash.Add(AttackComboCount);
            hash.Add(RemainingAttackInterval);
            hash.Add(NextAttackKind);
            AddHashCodes(hash, Abilities);
            hash.Add(Action);
            hash.Add(PrevActionState);
            hash.Add(MarchingLane);
            hash.Add(Pos);
            hash.Add(PrevPos);
            hash.Add(MoveStartCondition);
            hash.Add(MoveStopCondition);
            hash.Add(MoveRestartCondition);
            hash.Add(RemainingMoveLoopCount);
            hash.Add(LocatedKoma);
            hash.Add(PrevLocatedKoma);
            hash.Add(MoveStartedKoma);
            hash.Add(PosUpdateStageTickCount);
            hash.Add(MoveStopStageTickCount);
            hash.Add(MoveStartStageTickCount);
            AddHashCodes(hash, StateEffects);
            hash.Add(SummonedTickCount);
            hash.Add(SummonAnimationType);
            AddHashCodes(hash, SpeechBalloons);
            hash.Add(Transformation);
            hash.Add(DropBattlePoint);
            hash.Add(IsMoveStarted);
            hash.Add(IsMoveStopped);
            hash.Add(IsPrevMoveStopped);
            hash.Add(IsOutpostDamageInvalidation);
            hash.Add(Phantomized);
            hash.Add(AuraType);
            hash.Add(DeathType);
            hash.Add(DefeatedScore);

            return hash.ToHashCode();
        }

        static void AddHashCodes<T>(HashCode hash, IReadOnlyList<T> models)
        {
            if (models == null) return;

            int start = models.Count > 10 ? models.Count - 10 : 0;
            for (int i = start; i < models.Count; i++)
            {
                hash.Add(models[i]);
            }
        }
    }
}
