using System.Collections.Generic;
using GLOW.Scenes.Notice.Domain.Model;

namespace GLOW.Scenes.Notice.Domain.Factory
{
    public interface IDisplayNoticeListFactory
    {
        IReadOnlyList<NoticeModel> CreateDisplayNoticeList();
    }
}