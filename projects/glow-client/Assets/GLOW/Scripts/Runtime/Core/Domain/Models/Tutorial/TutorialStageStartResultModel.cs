namespace GLOW.Core.Domain.Models.Tutorial
{
    public record TutorialStageStartResultModel(
        UserInGameStatusModel UserInGameStatusModel,
        TutorialStatusModel TutorialStatusModel)
    {
        public static TutorialStageStartResultModel Empty { get; } = new TutorialStageStartResultModel(UserInGameStatusModel.Empty, TutorialStatusModel.Empty);
    }
}
