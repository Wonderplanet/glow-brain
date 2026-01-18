using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record StateKomaEffectModel(
            KomaId KomaId,
            KomaEffectType EffectType,
            KomaEffectTargetSide TargetSide,
            IReadOnlyList<CharacterColor> TargetColors,
            IReadOnlyList<CharacterUnitRoleType> TargetRoles,
            KomaEffectParameter EffectParameter)
        : BaseKomaEffectModel(
            KomaId,
            EffectType,
            TargetSide,
            TargetColors,
            TargetRoles)
    {
        public static StateKomaEffectModel Empty { get; } = new StateKomaEffectModel(
            KomaId.Empty,
            KomaEffectType.None,
            KomaEffectTargetSide.All,
            new List<CharacterColor>(),
            new List<CharacterUnitRoleType>(),
            KomaEffectParameter.Empty);

        static readonly Dictionary<KomaEffectType, List<StateEffectType>> StateEffectsThatBlockable = new()
        {
            { KomaEffectType.AttackPowerUp, new List<StateEffectType>() },
            { KomaEffectType.AttackPowerDown, new List<StateEffectType> { StateEffectType.AttackPowerDownKomaBlock } },
            { KomaEffectType.MoveSpeedUp, new List<StateEffectType>() },
            { KomaEffectType.SlipDamage, new List<StateEffectType>() },
        };

        static readonly Dictionary<KomaEffectType, List<StateEffectType>> StateEffectsThatBoost = new()
        {
            { KomaEffectType.AttackPowerUp, new List<StateEffectType> { StateEffectType.AttackPowerUpKomaBoost } },
            { KomaEffectType.AttackPowerDown, new List<StateEffectType>() },
            { KomaEffectType.MoveSpeedUp, new List<StateEffectType>() },
            { KomaEffectType.SlipDamage, new List<StateEffectType>() },
        };
        
        static readonly Dictionary<KomaEffectType, bool> StateEffectVisibleDictionary = new()
        {
            { KomaEffectType.AttackPowerUp, true },
            { KomaEffectType.AttackPowerDown, true },
            { KomaEffectType.MoveSpeedUp, true },
            { KomaEffectType.SlipDamage, false },
        };

        public override IReadOnlyList<StateEffectType> GetStateEffectsThatBlockableThis()
        {
            return StateEffectsThatBlockable[EffectType];
        }

        public override IReadOnlyList<StateEffectType> GetStateEffectsThatBoostThis()
        {
            return StateEffectsThatBoost[EffectType];
        }
        
        public override bool IsStateEffectVisible()
        {
            return StateEffectVisibleDictionary[EffectType];
        }

        public override StateEffect GetStateEffect(BattleSide battleSide, IReadOnlyList<StateEffectParameter> boostParameters)
        {
            switch (EffectType)
            {
                case KomaEffectType.AttackPowerUp:
                    return CreateStateEffect(StateEffectType.AttackPowerUp, EffectParameter, boostParameters);
                case KomaEffectType.AttackPowerDown:
                    return CreateStateEffect(StateEffectType.AttackPowerDown, EffectParameter, boostParameters);
                case KomaEffectType.MoveSpeedUp:
                    return CreateStateEffect(StateEffectType.MoveSpeedUp, EffectParameter, boostParameters);
                case KomaEffectType.SlipDamage:
                    return CreateStateEffect(StateEffectType.SlipDamage, EffectParameter, boostParameters);
                default:
                    return StateEffect.Empty;
            }
        }

        public override bool ExistsStateEffect()
        {
            return true;
        }

        StateEffect CreateStateEffect(
            StateEffectType type,
            KomaEffectParameter parameter,
            IReadOnlyList<StateEffectParameter> boostParameters)
        {
            var boostPercentages = boostParameters.Select(param => param.ToPercentageM());
            var boostPercentage = PercentageM.Hundred + boostPercentages.Sum();
            var stateEffectParameter = parameter.ToStateEffectParameter() * boostPercentage;

            return new StateEffect(
                type,
                EffectiveCount.Infinity,
                EffectiveProbability.Hundred,
                TickCount.Infinity,
                stateEffectParameter,
                StateEffectConditionValue.Empty,
                StateEffectConditionValue.Empty);
        }
    }
}
