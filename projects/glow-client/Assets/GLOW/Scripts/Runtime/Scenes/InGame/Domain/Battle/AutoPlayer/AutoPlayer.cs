using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class AutoPlayer : IAutoPlayer
    {
        public const string PlayerAutoPlayerBindId = "AutoPlayer_Player";
        public const string EnemyAutoPlayerBindId = "AutoPlayer_Enemy";

        IAutoPlayerProcessor _autoPlayerProcessor = EmptyAutoPlayerProcessor.Instance;

        public AutoPlayerEnabledFlag IsEnabled { get; set; } = AutoPlayerEnabledFlag.False;
        public AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel =>
            _autoPlayerProcessor.CurrentAutoPlayerSequenceGroupModel;
        public AutoPlayerSequenceSummonCount BossCount => _autoPlayerProcessor.BossCount;

        public void SetAutoPlayerProcessor(IAutoPlayerProcessor autoPlayerProcessor)
        {
            _autoPlayerProcessor = autoPlayerProcessor;
        }

        public IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context)
        {
            return _autoPlayerProcessor.Tick(context);
        }

        public bool RemainsSummonUnitByOutpostDamage()
        {
            return _autoPlayerProcessor.RemainsSummonUnitByOutpostDamage();
        }
    }
}
