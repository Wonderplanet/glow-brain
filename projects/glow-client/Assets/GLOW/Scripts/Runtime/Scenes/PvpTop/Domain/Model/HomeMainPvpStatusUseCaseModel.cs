using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;

namespace GLOW.Scenes.PvpTop.Domain.Model
{
    public record HomeMainPvpStatusUseCaseModel(
        QuestContentOpeningStatusModel PvpQuestContentOpeningStatus,
        NotificationBadge PvpContentNotification
    );
}
