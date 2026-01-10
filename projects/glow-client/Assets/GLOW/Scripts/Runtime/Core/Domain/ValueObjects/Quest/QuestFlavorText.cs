using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Quest
{
    public record QuestFlavorText(ObscuredString Value)
    {
        public static QuestFlavorText Empty { get; } = new QuestFlavorText(String.Empty);
    };
}
