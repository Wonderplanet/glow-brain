using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Presentation.ViewModels;

namespace GLOW.Scenes.InGame.Presentation.Translators
{
    public class SpecialUnitSummonConfirmationDialogViewModelTranslator
    {
        public static SpecialUnitSummonConfirmationDialogViewModel Translate(SpecialUnitSummonConfirmationDialogUseCaseModel summonConfirmationDialogUseCaseModel)
        {
            return new SpecialUnitSummonConfirmationDialogViewModel(
                summonConfirmationDialogUseCaseModel.SpecialAttackName,
                summonConfirmationDialogUseCaseModel.SpecialAttackInfoDescription,
                summonConfirmationDialogUseCaseModel.SummonCost,
                summonConfirmationDialogUseCaseModel.NeedTargetSelectTypeFlag);
        }
    }
}
