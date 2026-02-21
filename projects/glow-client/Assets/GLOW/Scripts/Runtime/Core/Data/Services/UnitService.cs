using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class UnitService : IUnitService
    {
        [Inject] UnitApi UnitApi { get; }

        public async UniTask<UnitLevelUpResultModel> LevelUp(CancellationToken cancellationToken, UserDataId usrUnitId, UnitLevel level)
        {
            var resultData = await UnitApi.LevelUp(cancellationToken, usrUnitId.Value, level.Value);
            var unit = UserUnitDataTranslator.ToUserUnitModel(resultData.UsrUnit);
            var parameter = UserParameterTranslator.ToUserParameterModel(resultData.UsrParameter);
            return new UnitLevelUpResultModel(unit, parameter);
        }

        public async UniTask<UnitRankUpResultModel> RankUp(CancellationToken cancellationToken, UserDataId usrUnitId)
        {
            var resultData = await UnitApi.RankUp(cancellationToken, usrUnitId.Value);
            var unit = UserUnitDataTranslator.ToUserUnitModel(resultData.UsrUnit);
            var items = resultData.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            return new UnitRankUpResultModel(unit, items);
        }

        public async UniTask<UnitGradeUpResultModel> GradeUp(CancellationToken cancellationToken, UserDataId usrUnitId)
        {
            var resultData = await UnitApi.GradeUp(cancellationToken, usrUnitId.Value);
            var unit = UserUnitDataTranslator.ToUserUnitModel(resultData.UsrUnit);
            var items = resultData.UsrItems.Select(ItemDataTranslator.ToUserItemModel).ToList();
            return new UnitGradeUpResultModel(unit, items);
        }
    }
}
