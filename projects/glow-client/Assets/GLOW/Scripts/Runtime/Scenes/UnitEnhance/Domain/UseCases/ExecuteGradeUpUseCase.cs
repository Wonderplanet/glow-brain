using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class ExecuteGradeUpUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitService UnitService { get; }
        [Inject] IGameManagement GameManagement { get; }

        public async UniTask<UnitEnhanceGradeUpResultModel> ExecuteGradeUp(CancellationToken cancellationToken, UserDataId userUnitId)
        {
            var beforeUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var beforeGrade = beforeUnit.Grade;

            var result = await UnitService.GradeUp(cancellationToken, userUnitId);
            UpdateGameModel(result.UserUnit, result.UserItems);
            return new UnitEnhanceGradeUpResultModel(userUnitId, beforeGrade, result.UserUnit.Grade);
        }

        void UpdateGameModel(UserUnitModel unit, IReadOnlyList<UserItemModel> userItems)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();

            var newGameFetchOther = gameFetchOther with
            {
                UserItemModels = gameFetchOther.UserItemModels.Update(userItems),
                UserUnitModels = gameFetchOther.UserUnitModels.Update(unit)
            };

            GameManagement.SaveGameFetchOther(newGameFetchOther);
        }
    }
}
