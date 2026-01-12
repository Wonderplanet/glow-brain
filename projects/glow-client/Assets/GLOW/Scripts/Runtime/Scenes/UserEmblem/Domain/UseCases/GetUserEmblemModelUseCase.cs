using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserEmblem.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UserEmblem.Domain.UseCases
{
    public class GetUserEmblemModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }
        [Inject] IUserEmblemBadgeRepository UserEmblemBadgeRepository { get; }
        public HeaderUserEmblemModel GetHeaderUserEmblemModel()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userEmblems = gameFetchOther.UserEmblemModel;
            var mstEmblems = MstEmblemRepository.GetMstEmblems();
            var currentEmblemId = gameFetchOther.UserProfileModel.MstEmblemId;

            HeaderUserEmblemCellModel currentEmblemCellModel = HeaderUserEmblemCellModel.Empty;

            if (!currentEmblemId.IsEmpty())
            {
                var currentEmblem = mstEmblems.First(mstEmblem => mstEmblem.Id == currentEmblemId);
                currentEmblemCellModel = new HeaderUserEmblemCellModel(
                    currentEmblem.Id,
                    EmblemIconAssetPath.FromAssetKey(currentEmblem.AssetKey),
                    currentEmblem.Description,
                    new NotificationBadge(false));
            }

            var displayedEmblemIdList = UserEmblemBadgeRepository.DisplayedUserEmblemIds;

            bool isSeriesBadge = false;
            bool isEventBadge = false;

            List<HeaderUserEmblemCellModel> seriesEmblems = new();
            List<HeaderUserEmblemCellModel> eventEmblems = new();

            foreach (var mst in mstEmblems)
            {
                if (userEmblems.All(user => user.MstEmblemId != mst.Id)) continue;

                var isBadge = !displayedEmblemIdList.Contains(mst.Id);

                if (mst.EmblemType == EmblemType.Series)
                {
                    if (isBadge)
                        isSeriesBadge = true;

                    seriesEmblems.Add(new HeaderUserEmblemCellModel(
                        mst.Id,
                        EmblemIconAssetPath.FromAssetKey(mst.AssetKey),
                        mst.Description,
                        new NotificationBadge(isBadge)));
                }
                else //if (mst.EmblemType == EmblemType.Event)
                {
                    if (isBadge)
                        isEventBadge = true;

                    eventEmblems.Add(new HeaderUserEmblemCellModel(
                        mst.Id,
                        EmblemIconAssetPath.FromAssetKey(mst.AssetKey),
                        mst.Description,
                        new NotificationBadge(isBadge)));
                }
            }

            return new HeaderUserEmblemModel(currentEmblemCellModel, isSeriesBadge, isEventBadge, seriesEmblems, eventEmblems);
        }
    }
}
