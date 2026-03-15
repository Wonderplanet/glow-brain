using System;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.ValueObjects
{
    public record SpecialAttackCoolTime(TickCount Value)
    {
        public static SpecialAttackCoolTime Empty { get; } = new(TickCount.Zero);

        public String ToCoolTimeString()
        {
            return Value switch
            {
                var value when value.Value <= 500 => "とても早い",
                var value when value.Value <= 1050 => "早い",
                var value when value.Value <= 1500 => "遅い",
                _ => "とても遅い"
            };
        }
    }
}
