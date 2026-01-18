using System.Linq;
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

        public NotificationBadge GetUnitNotification(UserUnitModel userUnit)
        {
            return GetUnitGradeUpNotification(userUnit);
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
    }
}
