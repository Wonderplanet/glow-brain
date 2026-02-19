using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public interface IAutoPlayer
    {
        AutoPlayerEnabledFlag IsEnabled { get; set; }
        AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel { get; }
        AutoPlayerSequenceSummonCount BossCount { get; }

        void SetAutoPlayerProcessor(IAutoPlayerProcessor autoPlayerProcessor);

        IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context);

        bool RemainsSummonUnitByOutpostDamage();
    }
}

