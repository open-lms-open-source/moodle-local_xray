# Blackboard X-Ray Learning Analytics local plugin

## Requirements

* [Moodle 2.9+][moodle-29]
* [Moodlerooms Framework][mr-framework-github]
* [AWS PHP SDK][mr-aws-sdk]
* Purchased licence of X-Ray Learning Analytics service ( For more information please visit the official [site][xray-site]. ) 

## Installation

* Install Moodlerooms Framework
* Install AWS SDK
* Deploy and install X-Ray Learning Analytics plugin

### Moodlerooms Framework

Moodlerooms Framework can be obtained from [Moodle plugins][mr-framework-moodle] database or from our [GitHub repository][mr-framework-github]. Either way is suitable. Make sure to choose the correct version of the plugin for your version of Moodle.

### AWS SDK

This plugin is required in order to enable support for exporting data from Moodle into X-Ray for further analysis. You can get it only from our [GitHub repository][mr-aws-sdk].

Choose based on the following table:

<table id="aws_sdk_table">
  <caption><strong>AWS SDK versions</strong></caption>
  <tr>
    <th>Moodle</th>
    <th>PHP</th>
    <th>AWS repository branch</th>
  </tr>
  <tr>
    <td>2.9.x - 3.1.x</td>
    <td>5.5+</td>
    <td><a href="https://github.com/moodlerooms/moodle-local_aws_sdk/tree/MOODLE_31_STABLE">MOODLE_31_STABLE</a></td>
  </tr>
  <tr>
    <td>3.2.x+</td>
    <td>5.6.5+</td>
    <td><a href="https://github.com/moodlerooms/moodle-local_aws_sdk/tree/MOODLE_32_STABLE">MOODLE_32_STABLE</a></td>
  </tr>
  <tr>
    <td>2.9.x - 3.1.x</td>
    <td>5.4.x</td>
    <td><a href="https://github.com/moodlerooms/moodle-local_aws_sdk/tree/LEGACY">LEGACY</a></td>
  </tr>
</table>

Suggested way of deploying would be to clone the aws sdk repository into appropriate location within your Moodle code base and checkout desired branch. That way you can also easily deploy updates if needed.

Example:

    git clone https://github.com/moodlerooms/moodle-local_aws_sdk.git [/path/to/moodle]/local/aws_sdk
    git --git-dir="[/path/to/moodle]/local/aws_sdk/.git/" --work-tree="[/path/to/moodle]/local/aws_sdk" checkout -b MOODLE_31_STABLE origin/MOODLE_31_STABLE

## Configuration

Contact the Blackboard X-Ray specialists about configuration information and onboarding process.

## Copyright

&copy; Blackboard, Inc.  Code for this plugin is licensed under the [GPLv3 license][GPLv3].

Any Blackboard trademarks and logos included in these plugins are property of Blackboard and should not be reused, redistributed, modified, repurposed, or otherwise altered or used outside of this plugin.

[xray-site]: http://www.blackboard.com/education-analytics/xray-learning-analytics.aspx "X-Ray Analytics"
[moodle-29]: https://docs.moodle.org/dev/Moodle_2.9_release_notes "Moodle 2.9 Release Notes"
[mr-framework-github]: https://github.com/moodlerooms/moodle-local_mr "Moodlerooms Framework"
[mr-framework-moodle]: https://moodle.org/plugins/view.php?plugin=local_mr "Moodlerooms Framework"
[mr-aws-sdk]: https://github.com/moodlerooms/moodle-local_aws_sdk "AWS SDK"
[mr-aws-sdk-31-branch]: https://github.com/moodlerooms/moodle-local_aws_sdk/tree/MOODLE_31_STABLE "3.1 SDK"
[mr-aws-sdk-32-branch]: https://github.com/moodlerooms/moodle-local_aws_sdk/tree/MOODLE_32_STABLE "3.2 SDK"
[mr-aws-sdk-legacy-branch]: https://github.com/moodlerooms/moodle-local_aws_sdk/tree/LEGACY "Legacy SDK"
[GPLv3]: http://www.gnu.org/licenses/gpl-3.0.html "GNU General Public License"