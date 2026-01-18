using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Announcement;

namespace GLOW.Core.Domain.Services
{
    public interface IAnnouncementService
    {
        UniTask<AnnouncementResultModel> Index(CancellationToken cancellationToken, DateTimeOffset nowTime);
        
        UniTask<AnnouncementLastUpdatedModel> LastUpdated(CancellationToken cancellationToken, DateTimeOffset nowTime);
    }
}