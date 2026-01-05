using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel
{
    // コマ内のキャラに毎フレーム、コマから出ても効果が持続するStateEffectを付与するコマ効果
    public record PersistentStateKomaEffectModel(
        KomaId KomaId,
        StateEffectSourceId StateEffectSourceId,
        KomaEffectType EffectType,
        KomaEffectTargetSide TargetSide,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles,
        TickCount RemainingDuration,
        StateEffectParameter EffectParameter)
        : BaseKomaEffectModel(
            KomaId,
            EffectType,
            TargetSide,
            TargetColors,
            TargetRoles)
    {
        public static readonly KomaEffectType[] PersistentKomaTypes =
        {
            KomaEffectType.Poison,
            KomaEffectType.Burn,
            KomaEffectType.Weakening
        };

        // EffectTypeに応じて付与処理を変える
        IPersistentKomaEffectLogic Logic { get; } = EffectType switch
        {
            KomaEffectType.Poison => new PoisonKomaEffectLogic(),
            KomaEffectType.Burn => new BurnKomaEffectLogic(),
            KomaEffectType.Weakening => new WeakeningKomaEffectLogic(),
            _ => throw new System.ArgumentOutOfRangeException(nameof(EffectType), EffectType, null)
        };

        public static bool IsPersistentKomaEffect(KomaEffectType effectType)
        {
            return PersistentKomaTypes.Contains(effectType);
        }

        public override IReadOnlyList<StateEffectType> GetStateEffectsThatBlockableThis()
        {
            return Logic.GetBlockableStateEffectTypes();
        }

        public override bool ExistsStateEffect()
        {
            return true;
        }

        public override bool IsAlwaysActive()
        {
            return true;
        }

        public override bool IsStateEffectVisible()
        {
            return true;
        }

        public override StateEffect GetStateEffect(BattleSide battleSide,
            IReadOnlyList<StateEffectParameter> boostParameters)
        {
            return Logic.GetStateEffect(EffectParameter);
        }

        public override bool IsTarget(CharacterUnitModel unit)
        {
            if (!base.IsTarget(unit)) return false;
            return Logic.IsTarget(unit, StateEffectSourceId);
        }

        public StateEffectType GetStateEffectType()
        {
            return Logic.GetStateEffectType();
        }

        public IStateEffectModel UpdateDuration(IStateEffectModel stateEffectModel, TickCount duration)
        {
            return Logic.UpdateDuration(stateEffectModel, duration);
        }
    }
}
