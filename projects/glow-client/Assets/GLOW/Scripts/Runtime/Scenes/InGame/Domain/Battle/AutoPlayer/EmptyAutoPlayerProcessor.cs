using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class EmptyAutoPlayerProcessor : IAutoPlayerProcessor
    {
        public static EmptyAutoPlayerProcessor Instance { get;  } = new EmptyAutoPlayerProcessor();

        static readonly IReadOnlyList<IAutoPlayerAction> EmptyActionList = new List<IAutoPlayerAction>();

        public AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel => AutoPlayerSequenceGroupModel.Empty;
        public AutoPlayerSequenceSummonCount BossCount => AutoPlayerSequenceSummonCount.Empty;

        public void Setup(
            MstAutoPlayerSequenceModel autoPlayerSequenceModel,
            BattleSide battleSide,
            MstPageModel mstPageModel,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IAutoPlayerSequenceElementStateModelFactory stateModelFactory,
            IInitialCharacterUnitCoefFactory coefFactory)
        {
        }

        public IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context)
        {
            return EmptyActionList;
        }

        public bool RemainsSummonUnitByOutpostDamage()
        {
            return false;
        }
    }
}

