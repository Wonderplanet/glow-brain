using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.Models;
using GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceGradeUpConfirmDialog.Domain.UseCases
{
    public class GetUnitEnhanceGradeUpConfirmModelUseCase
    {

        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IMstUnitGradeUpRepository MstUnitGradeUpRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] ISpecialAttackInfoModelFactory SpecialAttackInfoModelFactory { get; }

        public UnitEnhanceGradeUpConfirmModel GetModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            var requireMstItem = MstItemDataRepository.GetItem(mstUnit.FragmentMstItemId);
            var requireUserItem = GameRepository.GetGameFetchOther().UserItemModels
                .FirstOrDefault(item => item.MstItemId == mstUnit.FragmentMstItemId, UserItemModel.Empty);

            var beforeGrade = userUnit.Grade;
            var afterMstGrade = MstUnitGradeUpRepository.GetUnitGradeUpList(mstUnit.UnitLabel)
                .Where(grade => userUnit.Grade < grade.GradeLevel)
                .OrderBy(grade => grade.GradeLevel)
                .FirstOrDefault();

            if (afterMstGrade == null) return null;

            var itemModel = ItemModelTranslator.ToItemModel(requireMstItem, afterMstGrade.RequireAmount);

            var beforeStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, beforeGrade);
            var afterStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, userUnit.Rank, afterMstGrade.GradeLevel);

            var specialAttackInfoModel = SpecialAttackInfoModelFactory.Create(mstUnit, afterMstGrade.GradeLevel, userUnit.Level);

            var enableConfirm = UnitGradeUpEnableConfirm.Create(requireUserItem.Amount - afterMstGrade.RequireAmount >= ItemAmount.Zero);

            return new UnitEnhanceGradeUpConfirmModel(
                mstUnit.RoleType,
                itemModel,
                userUnit.Grade,
                afterMstGrade.GradeLevel,
                requireUserItem.Amount,
                beforeStatus.HP,
                afterStatus.HP,
                beforeStatus.AttackPower,
                afterStatus.AttackPower,
                specialAttackInfoModel.Name,
                specialAttackInfoModel.Description,
                enableConfirm);
        }
    }
}
