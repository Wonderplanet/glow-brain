namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialUnitLevelUpResultModel(
        TutorialStatusModel TutorialStatusModel,
        UserUnitModel UserUnitModel, 
        UserParameterModel UserParameterModel);
}