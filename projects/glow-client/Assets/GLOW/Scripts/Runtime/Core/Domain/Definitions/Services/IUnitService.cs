using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Unit;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IUnitService
    {
        UniTask<UnitLevelUpResultModel> LevelUp(CancellationToken cancellationToken, UserDataId usrUnitId, UnitLevel level);
        UniTask<UnitRankUpResultModel> RankUp(CancellationToken cancellationToken, UserDataId usrUnitId);
        UniTask<UnitGradeUpResultModel> GradeUp(CancellationToken cancellationToken, UserDataId usrUnitId);
    }
}
