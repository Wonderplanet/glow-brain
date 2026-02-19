using GLOW.Core.Domain.Models.OprData;
using GLOW.Scenes.Notice.Domain.Model;

namespace GLOW.Scenes.Notice.Domain.Factory
{
    public interface IInGameNoticeModelFactory
    {
        NoticeModel Create(OprNoticeModel model);
    }
}