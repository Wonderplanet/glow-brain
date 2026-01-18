using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;


namespace GLOW.Core.Domain.UseCases
{
    public class CheckContentMaintenanceUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public bool IsInMaintenance(ContentMaintenanceTarget[] targets)
        {
            if (targets == null || targets.IsEmpty()) return false;


            // 全てのタイプがメンテナンス中かチェック
            var allInMaintenance = true;
            foreach (var target in targets)
            {
                if (GetMngContentCloseModels(target.Type, target.Id).IsEmpty())
                {
                    allInMaintenance = false;
                    break;
                }
            }
            return allInMaintenance;
        }

        IReadOnlyList<MngContentCloseModel> GetMngContentCloseModels(
                    ContentMaintenanceType type,
                    MasterDataId id)
        {
            var mngContentCloseModels = GameRepository.GetGameFetchOther().MngContentCloseModels;

            // タイプと期間でフィルタリング
            var now = TimeProvider.Now;
            mngContentCloseModels = mngContentCloseModels.Where(x =>
                x.ContentMaintenanceType == type
                && x.StartAt <= now
                && now <= x.EndAt
                ).ToList();

            // ID指定による追加フィルタリング
            if (!id.IsEmpty())
            {
                // ID指定がある場合は特定のIDがメンテ対象か確認(ガシャ想定)
                mngContentCloseModels = mngContentCloseModels
                    .Where(x => !x.ContentId.IsEmpty() && x.ContentId.Equals(id))
                    .ToList();
            }
            else
            {
                // ID指定がない場合はContentIdが空のもののみを対象
                mngContentCloseModels = mngContentCloseModels
                    .Where(x => x.ContentId.IsEmpty())
                    .ToList();
            }

            return mngContentCloseModels;
        }
    }
}
