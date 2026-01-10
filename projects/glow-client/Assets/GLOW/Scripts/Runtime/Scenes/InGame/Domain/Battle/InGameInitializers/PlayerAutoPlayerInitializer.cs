using System.Collections.Generic;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class PlayerAutoPlayerInitializer : IPlayerAutoPlayerInitializer
    {
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject(Id = AutoPlayer.AutoPlayer.PlayerAutoPlayerBindId)] IAutoPlayer PlayerAutoPlayer { get; }
        [Inject] IDeckUnitSummonEvaluator DeckUnitSummonEvaluator { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IDeckUnitSpecialAttackEvaluator DeckUnitSpecialAttackEvaluator { get; }

        public void Initialize(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            var summonSelector = new DeckAutoPlayerSummonSelector(deckUnits);

            var autoPlayerProcessor = new DeckAutoPlayerProcessor(
                summonSelector,
                DeckUnitSummonEvaluator,
                DeckSpecialUnitSummonEvaluator,
                DeckUnitSpecialAttackEvaluator,
                BattleSide.Player);

            PlayerAutoPlayer.SetAutoPlayerProcessor(autoPlayerProcessor);
            PlayerAutoPlayer.IsEnabled = InGamePreferenceRepository.IsInGameAutoEnabled.ToAutoPlayerEnabledFlag();
        }
    }
}
