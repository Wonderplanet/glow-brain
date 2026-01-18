namespace GLOW.Scenes.Title.Domains.UseCase
{
    public record StageSessionResumeUseCaseModel(
        StageSessionOpenFlag IsOpenStage,
        SessionAbortConfirmAttentionText SessionAbortConfirmAttentionText
    );
}