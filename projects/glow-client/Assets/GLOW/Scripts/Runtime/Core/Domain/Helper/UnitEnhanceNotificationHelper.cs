using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Core.Domain.Helper
{
    public class UnitEnhanceNotificationHelper : IUnitEnhanceNotificationHelper
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstUnitGradeUpRepository MstUnitGradeUpRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitGradeUpRewardRepository MstUnitGradeUpRewardRepository { get; }

        public NotificationBadge GetUnitNotification(UserUnitModel userUnit)
        {
            var gradeUpNotification = GetUnitGradeUpNotification(userUnit);
            var gradeUpArtworkRewardNotification = GetUnitGradeUpArtworkRewardNotification(userUnit);

            return new NotificationBadge(gradeUpNotification ||
                                         gradeUpArtworkRewardNotification);
        }

        public NotificationBadge GetUnitGradeUpNotification(UserUnitModel userUnit)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
            var mstUnitGradeUps = MstUnitGradeUpRepository.GetUnitGradeUpList(mstUnit.UnitLabel);
            var nextGrade = mstUnitGradeUps.Where(mst => userUnit.Grade < mst.GradeLevel)
                .OrderBy(mst => mst.GradeLevel)
                .FirstOrDefault();
            var fragmentItem = GameRepository.GetGameFetchOther().UserItemModels.Find(item => item.MstItemId == mstUnit.FragmentMstItemId);
            if (nextGrade == null || fragmentItem == null) return NotificationBadge.False;

            return new NotificationBadge(nextGrade.RequireAmount <= fragmentItem.Amount);
        }

        public CheckUnitGradeUpArtworkRewardFlag GetUnitGradeUpArtworkRewardNotification(UserUnitModel userUnit)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            var mstUnitGradeUpReward = MstUnitGradeUpRewardRepository.GetUnitGradeUpRewardFirstOrDefault(mstUnit.Id);
            // 万が一原画でない場合はfalse
            if (mstUnitGradeUpReward.IsEmpty() || mstUnitGradeUpReward.ResourceType != ResourceType.Artwork)
            {
                return CheckUnitGradeUpArtworkRewardFlag.False;
            }

            // userUnitのグレードが報酬獲得のグレードよりも大きい && 報酬受け取りの最終更新グレードが報酬のグレード以下の場合
            if (mstUnitGradeUpReward.GradeLevel <= userUnit.Grade &&
                mstUnitGradeUpReward.GradeLevel > userUnit.LastRewardGrade)
            {
                return CheckUnitGradeUpArtworkRewardFlag.True;
            }

            return CheckUnitGradeUpArtworkRewardFlag.False;
        }
    }
}
