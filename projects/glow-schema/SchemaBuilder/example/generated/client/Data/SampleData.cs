using System;
using System.Collections;
using System.Collections.Generic;

namespace Data
{
	[Serializable]
	public class SampleData
	{
		public int id;
		public string name;
		public EnumName enumData;
		public SampleSubData subData;
		public SampleSubData[] subDataList;
	}
}
