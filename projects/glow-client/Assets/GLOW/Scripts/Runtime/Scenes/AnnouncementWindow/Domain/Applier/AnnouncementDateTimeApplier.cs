using System;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Enum;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.Applier
{
    public class AnnouncementDateTimeApplier : IAnnouncementDateTimeApplier
    {
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }

        void IAnnouncementDateTimeApplier.UpdateAnnouncementLastUpdatedDateTimeAtLogin(
            DateTimeOffset loginDateTime,
            AnnouncementLastUpdateAt informationLastUpdateAt,
            AnnouncementLastUpdateAt operationLastUpdateAt,
            AnnouncementDisplayMeansType displayMeansType)
        {
            AnnouncementPreferenceRepository.SetInformationLastUpdated(informationLastUpdateAt);
            AnnouncementPreferenceRepository.SetOperationLastUpdated(operationLastUpdateAt);

            if (displayMeansType == AnnouncementDisplayMeansType.Login)
            {
                // ログイン時のみ最終表示時間を更新
                // 1日1回ログインが残っている状態でタイトルのメニューを表示した場合でもログイン時に表示するため
                AnnouncementPreferenceRepository.SetAnnouncementLastDisplayDateTimeOffsetAtLogin(loginDateTime);
            }
        }
    }
}
