using GLOW.Core.Domain.Constants;

namespace GLOW.Scenes.Mission.Presentation.Extension
{
    public class MissionTypeExtension
    {
        public static string MissionTypeToMissionTypeName(MissionType type)
        {
            return type switch
            {
                MissionType.Achievement => "アチーブメントミッション",
                MissionType.DailyBonus => "ログインボーナス",
                MissionType.Daily => "デイリーミッション",
                MissionType.Weekly => "ウィークリーミッション",
                MissionType.Event => "いいジャン祭ミッション",
                MissionType.EventDailyBonus => "いいジャン祭限定ログインボーナス",
                _ => ""
            };
        }
    }
}