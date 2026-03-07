using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;

namespace GLOW.Core.Domain.Repositories
{
    // TODO: キャッシュしてから更新された内容の判断が出来ない
    //       ので、これを使わない(更新検知できる)ようにする
    public interface IAnnouncementCacheRepository
    {
        AnnouncementCellUseCaseModel Get(AnnouncementId masterDataId);
        void SetInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated);
        void SetOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated);
        void SetReadAnnouncementDictionary(IReadOnlyDictionary<AnnouncementId, AnnouncementCellUseCaseModel> readAnnouncementDictionary);
        AnnouncementLastUpdateAt GetInformationLastUpdated();
        AnnouncementLastUpdateAt GetOperationLastUpdated();
        IReadOnlyList<AnnouncementCellUseCaseModel> GetReadAnnouncementList();
    }
}
