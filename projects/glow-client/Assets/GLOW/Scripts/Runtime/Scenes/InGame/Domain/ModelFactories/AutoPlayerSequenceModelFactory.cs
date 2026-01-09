using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public class AutoPlayerSequenceModelFactory : IAutoPlayerSequenceModelFactory
    {
        [Inject] IMstAutoPlayerSequenceRepository MstAutoPlayerSequenceRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public AutoPlayerSequenceModel Create(AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId)
        {
            var mstModel = MstAutoPlayerSequenceRepository.GetMstAutoPlayerSequence(mstAutoPlayerSequenceSetId);

            var enemySummonElementGroup = mstModel.EnemySummonElements
                .GroupBy(element => element.Action.Value)
                .Select(group => new
                {
                    MstEnemyStageParameterModel = MstEnemyCharacterDataRepository.GetEnemyStageParameter(group.Key.ToMasterDataId()),
                    MstAutoPlayerSequenceElementModels = group
                })
                .ToList();

            var summonEnemies = enemySummonElementGroup
                .Select(group => group.MstEnemyStageParameterModel)
                .ToList();
            
            var bossCount = enemySummonElementGroup
                .Where(group => group.MstEnemyStageParameterModel.IsBoss)
                .SelectMany(group => group.MstAutoPlayerSequenceElementModels)
                .Aggregate(
                    AutoPlayerSequenceSummonCount.Empty, 
                    (current, element) => current + element.SummonCount
                );

            return new AutoPlayerSequenceModel(mstModel, summonEnemies, bossCount);
        }
    }
}