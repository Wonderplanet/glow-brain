using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;

namespace GLOW.Core.Data.Repositories
{
    public class AnnouncementCacheRepository : IAnnouncementCacheRepository
    {
        AnnouncementLastUpdateAt _informationLastUpdated = AnnouncementLastUpdateAt.Empty;
        AnnouncementLastUpdateAt _operationLastUpdated = AnnouncementLastUpdateAt.Empty;
        IReadOnlyDictionary<AnnouncementId, AnnouncementCellUseCaseModel> _readAnnouncementDictionary = new Dictionary<AnnouncementId, AnnouncementCellUseCaseModel>();

        AnnouncementCellUseCaseModel IAnnouncementCacheRepository.Get(AnnouncementId masterDataId)
        {
            var model = _readAnnouncementDictionary.TryGetValue(masterDataId, out var announcementCellUseCaseModel)
                ? announcementCellUseCaseModel
                : AnnouncementCellUseCaseModel.Empty;
            
            return model;
        }

        void IAnnouncementCacheRepository.SetInformationLastUpdated(AnnouncementLastUpdateAt informationLastUpdated)
        {
            _informationLastUpdated = informationLastUpdated;
        }

        void IAnnouncementCacheRepository.SetOperationLastUpdated(AnnouncementLastUpdateAt operationLastUpdated)
        {
            _operationLastUpdated = operationLastUpdated;
        }

        void IAnnouncementCacheRepository.SetReadAnnouncementDictionary(IReadOnlyDictionary<AnnouncementId, AnnouncementCellUseCaseModel> readAnnouncementDictionary)
        {
            _readAnnouncementDictionary = readAnnouncementDictionary;
        }

        AnnouncementLastUpdateAt IAnnouncementCacheRepository.GetInformationLastUpdated()
        {
            return _informationLastUpdated;
        }

        AnnouncementLastUpdateAt IAnnouncementCacheRepository.GetOperationLastUpdated()
        {
            return _operationLastUpdated;
        }
        
        IReadOnlyList<AnnouncementCellUseCaseModel> IAnnouncementCacheRepository.GetReadAnnouncementList()
        {
            return _readAnnouncementDictionary.Values.ToList();
        }

    }
}
