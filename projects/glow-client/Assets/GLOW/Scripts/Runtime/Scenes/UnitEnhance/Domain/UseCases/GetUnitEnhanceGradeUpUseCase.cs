using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class GetUnitEnhanceGradeUpUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitGradeUpRepository MstUnitGradeUpRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceNotificationHelper UnitEnhanceNotificationHelper { get; }

        public UnitEnhanceGradeUpModel GetGradeUpModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            var requireMstItem = MstItemDataRepository.GetItem(mstUnit.FragmentMstItemId);
            var requireUserItem = GameRepository.GetGameFetchOther().UserItemModels.Find(item => item.MstItemId == mstUnit.FragmentMstItemId);
            var requireItemModel = requireUserItem != null
                ? ItemModelTranslator.ToItemModel(requireUserItem, requireMstItem)
                : ItemModelTranslator.ToItemModel(requireMstItem);

            var beforeStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, userUnit.Grade);

            var afterGrade = MstUnitGradeUpRepository.GetUnitGradeUpList(mstUnit.UnitLabel)
                .Where(grade => userUnit.Grade < grade.GradeLevel)
                .OrderBy(grade => grade.GradeLevel)
                .FirstOrDefault();
            var afterStatus = beforeStatus;
            if (afterGrade != null)
            {
                afterStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, afterGrade.GradeLevel);
            }

            var isGradeUp = UnitEnhanceNotificationHelper.GetUnitGradeUpNotification(userUnit);

            return new UnitEnhanceGradeUpModel(
                requireItemModel,
                afterGrade?.RequireAmount ?? ItemAmount.Empty,
                requireUserItem?.Amount ?? ItemAmount.Empty,
                requireMstItem.Name,
                beforeStatus.HP,
                afterStatus.HP,
                beforeStatus.AttackPower,
                afterStatus.AttackPower,
                userUnit.Grade,
                isGradeUp
            );
        }
    }
}
