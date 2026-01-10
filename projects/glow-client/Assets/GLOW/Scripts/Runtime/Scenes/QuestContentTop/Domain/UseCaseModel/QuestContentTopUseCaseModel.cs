namespace GLOW.Scenes.QuestContentTop.Domain.UseCaseModel
{
    public record QuestContentTopUseCaseModel(
        QuestContentTopSectionUseCaseModel EventSection,
        QuestContentTopSectionUseCaseModel DailySection,
        QuestContentTopSectionUseCaseModel EndContentSection,
        QuestContentTopSectionUseCaseModel PvPSection)
    {
        public static QuestContentTopUseCaseModel Empty { get; } = new(
            QuestContentTopSectionUseCaseModel.Empty,
            QuestContentTopSectionUseCaseModel.Empty,
            QuestContentTopSectionUseCaseModel.Empty,
            QuestContentTopSectionUseCaseModel.Empty);
    };
}
