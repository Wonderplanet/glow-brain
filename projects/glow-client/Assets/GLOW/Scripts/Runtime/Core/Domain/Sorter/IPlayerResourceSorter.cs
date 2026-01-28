using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Core.Domain.Sorter
{
    public interface IPlayerResourceSorter
    {
        IEnumerable<PlayerResourceModel> Sort(IEnumerable<PlayerResourceModel> rewards);
        IEnumerable<CommonReceiveResourceModel> Sort(IEnumerable<CommonReceiveResourceModel> resources);
    }
}