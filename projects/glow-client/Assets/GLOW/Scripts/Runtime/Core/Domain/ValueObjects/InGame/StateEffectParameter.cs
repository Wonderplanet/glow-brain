using GLOW.Core.Domain.Constants;
using UnityEngine;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.InGame
{
    /// <summary>
    /// キャラや拠点にかかる効果のパラメータ
    /// </summary>
    public record StateEffectParameter(ObscuredDecimal Value )
    {
        public static StateEffectParameter Empty { get; } = new(0);

        public ObscuredDecimal Value { get; } = Value > 0 ? Value : 0;

        public static bool operator <(StateEffectParameter a, StateEffectParameter b)
        {
            return a.Value < b.Value;
        }

        public static bool operator <=(StateEffectParameter a, StateEffectParameter b)
        {
            return a.Value <= b.Value;
        }

        public static bool operator >(StateEffectParameter a, StateEffectParameter b)
        {
            return a.Value > b.Value;
        }

        public static bool operator >=(StateEffectParameter a, StateEffectParameter b)
        {
            return a.Value >= b.Value;
        }

        public static StateEffectParameter operator + (StateEffectParameter a, StateEffectParameter b)
        {
            return new StateEffectParameter(a.Value + b.Value);
        }

        public static StateEffectParameter operator - (StateEffectParameter a, StateEffectParameter b)
        {
            return new StateEffectParameter(a.Value - b.Value);
        }

        public static StateEffectParameter operator *(StateEffectParameter a, PercentageM b)
        {
            var value = (float)(a.Value * b.Value / 100) * 100;
            return new StateEffectParameter((decimal)Mathf.Floor(value) / 100);
        }

        public string ToStringF2()
        {
            return Value.ToString("F2", null);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public PercentageM ToPercentageM()
        {
            return new PercentageM(Value);
        }

        public AttackPowerParameter ToFixedAttackPowerParameter()
        {
            return new AttackPowerParameter(AttackPowerParameterType.Fixed, (float)ToDecimal());
        }

        public AttackPowerParameter ToMaxHpPercentageAttackPowerParameter()
        {
            return new AttackPowerParameter(AttackPowerParameterType.MaxHpPercentage, (float)ToDecimal());
        }

        public TickCount ToTickCount()
        {
            return new TickCount((long)ToDecimal());
        }

        decimal ToDecimal()
        {
            // ObscuredDecimalを直接別の型にcastできないので一度decimalに変換する
            return Value;
        }
    }
}
