using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugMstUnitStatusUseCaseModel(
        DebugMstUnitAttackStatusUseCaseModel AttackStatus,
        IReadOnlyList<DebugMstLevelStatusUseCaseModel> LevelStatuses,
        DebugMstUnitSpecialUnitSpecialParamUseCaseModel SpecialUnitSpecialParam
        );

    public class DebugMstUnitStatusUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IDebugMstUnitAttackStatusModelFactory DebugMstUnitAttackStatusModelFactory { get; }
        [Inject] IDebugMstUnitLevelStatusModelFactory DebugMstUnitLevelStatusModelFactory { get; }
        [Inject] IDebugMstUnitSpecialUnitSpecialParamModelFactory DebugMstUnitSpecialUnitSpecialParamModelFactory { get; }

        public IReadOnlyList<DebugMstUnitStatusUseCaseModel> GetModels()
        {
            return MstCharacterDataRepository.GetCharacters()
                .Select(CreateDebugMstUnitStatusElementModel)
                .ToList();

        }

        DebugMstUnitStatusUseCaseModel CreateDebugMstUnitStatusElementModel(MstCharacterModel mstCharacterModel)
        {
            var attackStatus = DebugMstUnitAttackStatusModelFactory.Create(mstCharacterModel);
            var levelStatuses = DebugMstUnitLevelStatusModelFactory.Create(mstCharacterModel);
            var specialUnitSpecialParam = DebugMstUnitSpecialUnitSpecialParamModelFactory.Create(mstCharacterModel);
            return new DebugMstUnitStatusUseCaseModel(attackStatus, levelStatuses, specialUnitSpecialParam);
        }
    }
}
