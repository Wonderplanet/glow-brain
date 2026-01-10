using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public interface IAutoPlayerProcessor
    {
        AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel { get; }
        AutoPlayerSequenceSummonCount BossCount { get; }

        IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context);

        bool RemainsSummonUnitByOutpostDamage();
    }
}
