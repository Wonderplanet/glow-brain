using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Battle.AutoPlayer;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class EnemyAutoPlayerInitializer : IEnemyAutoPlayerInitializer
    {
        [Inject] InitialEnemyCharacterCoefFactory InitialCharacterUnitCoefFactory { get; }
        [Inject] IAutoPlayerSequenceElementStateModelFactory AutoPlayerSequenceElementStateModelFactory { get; }
        [Inject] IAutoPlayerSequenceModelFactory AutoPlayerSequenceModelFactory { get; }
        [Inject] IDeckUnitSummonEvaluator DeckUnitSummonEvaluator { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] IDeckUnitSpecialAttackEvaluator DeckUnitSpecialAttackEvaluator { get; }

        [Inject(Id = AutoPlayer.AutoPlayer.EnemyAutoPlayerBindId)]
        IAutoPlayer EnemyAutoPlayer { get; }

        public MstAutoPlayerSequenceModel Initialize(
            AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId,
            MstPageModel mstPageModel,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IReadOnlyList<DeckUnitModel> deckUnits)
        {
            if (deckUnits == null || deckUnits.Count == 0)
            {
                // Pvp以外のクエストでのAutoPlayerSequence設定対応
                return InitializeAutoPlayerSequence(
                    mstAutoPlayerSequenceSetId,
                    mstPageModel,
                    stageEnemyParameterCoef);
            }
            else
            {
                // Pvpでの対戦相手用のDeckAutoPlayer設定対応
                InitializeDeckAutoPlayer(deckUnits);
                return MstAutoPlayerSequenceModel.Empty;
            }
        }

        MstAutoPlayerSequenceModel InitializeAutoPlayerSequence(
            AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId,
            MstPageModel mstPageModel,
            IStageEnemyParameterCoef stageEnemyParameterCoef)
        {
            var autoPlayerSequenceModel = AutoPlayerSequenceModelFactory.Create(mstAutoPlayerSequenceSetId);
            var autoPlayerProcessor = new SequenceAutoPlayerProcessor();

            autoPlayerProcessor.Setup(
                autoPlayerSequenceModel,
                BattleSide.Enemy,
                mstPageModel,
                stageEnemyParameterCoef,
                AutoPlayerSequenceElementStateModelFactory,
                InitialCharacterUnitCoefFactory);

            EnemyAutoPlayer.SetAutoPlayerProcessor(autoPlayerProcessor);
            EnemyAutoPlayer.IsEnabled = AutoPlayerEnabledFlag.True;
            return autoPlayerSequenceModel.MstAutoPlayerSequenceModel;
        }

        void InitializeDeckAutoPlayer(IReadOnlyList<DeckUnitModel> deckUnits)
        {
            var summonSelector = new DeckAutoPlayerSummonSelector(deckUnits);

            var autoPlayerProcessor = new DeckAutoPlayerProcessor(
                summonSelector,
                DeckUnitSummonEvaluator,
                DeckSpecialUnitSummonEvaluator,
                DeckUnitSpecialAttackEvaluator,
                BattleSide.Enemy);

            EnemyAutoPlayer.SetAutoPlayerProcessor(autoPlayerProcessor);
            EnemyAutoPlayer.IsEnabled = AutoPlayerEnabledFlag.True;
        }
    }
}
