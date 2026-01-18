using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Modules.CommonReceiveView.Domain.Model;

namespace GLOW.Core.Domain.Sorter
{
    public class PlayerResourceSorter : IPlayerResourceSorter
    {
        public IEnumerable<PlayerResourceModel> Sort(IEnumerable<PlayerResourceModel> rewards)
        {
            return rewards
                .OrderBy(model => model.GroupSortOrder.Value)
                .ThenBy(model => model.SortOrder.Value);
        }

        public IEnumerable<CommonReceiveResourceModel> Sort(IEnumerable<CommonReceiveResourceModel> resources)
        {
            return resources
                .OrderBy(model => model.PlayerResourceModel.GroupSortOrder.Value)
                .ThenBy(model => model.PlayerResourceModel.SortOrder.Value);
        }
    }
}